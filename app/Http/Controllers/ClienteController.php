<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
    
        // Subconsulta para obtener la fecha de la última nota o interacción del cliente
        $query = DB::table('clientes')
            ->select('clientes.*', DB::raw('(SELECT MAX(created_at) FROM cliente_notas WHERE cliente_notas.cliente_id = clientes.id) as ultima_interaccion'))
            ->orderByRaw('COALESCE((SELECT MAX(created_at) FROM cliente_notas WHERE cliente_notas.cliente_id = clientes.id), clientes.created_at) DESC');
    
        // AISLAMIENTO: Si el usuario tiene empresa asignada, filtramos estrictamente por ella
        if (!empty($user->company_id)) {
            $query->where('clientes.company_id', $user->company_id);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('clientes.nombre', 'LIKE', "%{$search}%")
                  ->orWhere('clientes.telefono', 'LIKE', "%{$search}%")
                  ->orWhere('clientes.empresa', 'LIKE', "%{$search}%")
                  ->orWhere('clientes.correo', 'LIKE', "%{$search}%");
            });
        }
    
        $clientes = $query->paginate(15)->appends(['search' => $search]);
    
        if ($request->ajax()) {
            return view('agent.cliente.partials.table_body', compact('clientes'))->render();
        }
    
        return view('agent.cliente.index', compact('clientes', 'search'));
    }  

    // Mostrar la ficha del cliente y su historial de notas
    public function showByTelefono($telefono)
    {
        $user = Auth::user();
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        $ultimos10 = substr($telefonoLimpio, -10);

        $query = DB::table('clientes')
            ->where(function($q) use ($telefono, $ultimos10) {
                $q->where('telefono', $telefono)
                  ->orWhere('telefono', 'LIKE', '%' . $ultimos10);
            });

        // Filtrar por la empresa del usuario actual
        if (!empty($user->company_id)) {
            $query->where('company_id', $user->company_id);
        }

        $cliente = $query->first();

        // Solo se crea si NO existe en la empresa actual Y el número tiene una longitud real
        if (!$cliente && strlen($telefonoLimpio) >= 7) {
            $clienteId = DB::table('clientes')->insertGetId([
                'company_id' => $user->company_id ?? null,
                'nombre'     => 'Cliente Nuevo / Sin Registrar',
                'telefono'   => $telefono,
                'estado_crm' => 'Prospecto',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $clienteQuery = DB::table('clientes')->where('id', $clienteId);
            if (!empty($user->company_id)) {
                $clienteQuery->where('company_id', $user->company_id);
            }
            $cliente = $clienteQuery->first();
        } elseif (!$cliente) {
            return redirect()->back()->with('error', 'No se puede crear una ficha para extensiones internas o números de sistema.');
        }

        $notas = DB::table('cliente_notas')
            ->where('cliente_id', $cliente->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.cliente.ficha', compact('cliente', 'notas'));
    }
    
    public function triggerClickToCall(Request $request, $id)
    {
        $user = Auth::user();
    
        // 1. Buscamos al cliente dentro de su empresa
        $cliente = DB::table('clientes')
            ->where('id', $id)
            ->when(!empty($user->company_id), function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->first();
    
        if (!$cliente || empty($cliente->telefono)) {
            return response()->json([
                'success' => false, 
                'message' => 'Cliente no encontrado o sin número de teléfono.'
            ], 404);
        }
    
        // 2.  Obtenemos el secreto del nuevo campo en user_extensions del usuario logueado
        $userExtension = DB::table('user_extensions')
            ->where('user_id', $user->id)
            ->first();
        
        $sipSecret = $userExtension->asterisk_secret ?? '12345678910';        
    
        // 3. Preparamos los parámetros para tu API de Issabel
        $urlIssabelApi = "https://servidores.voiparkas.com/cdrweb/llamacrm/api_llamar_crm.php"; // <--- REEMPLAZA CON TU URL REAL
        
        $postData = [
            'token'   => $sipSecret,
            'numero'  => $cliente->telefono,
            'nombre'  => $cliente->nombre,
            'agente'  => $user->name ?? 'Agente'
        ];
    
        // 4. Consumimos tu API en Issabel mediante cURL
        $ch = curl_init($urlIssabelApi);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        // Limpiamos cualquier carácter basura o espacio antes/después del JSON
        $cleanResponse = trim($response);
        
        // Intentamos decodificar, si viene sucio buscamos si contiene la palabra "success"
        $resultadoApi = json_decode($cleanResponse, true);
    
        if ($httpCode === 200 || (is_string($response) && stripos($response, 'success') !== false)) {
            return response()->json([
                'success' => true, 
                'message' => 'Llamada iniciada correctamente.'
            ]);
        } else {
            return response()->json([
                'success' => false, 
                'message' => 'Issabel rechazó la llamada.'
            ], 400);
        }
    }   

    // Guardar un nuevo cliente desde el modal
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nombre'     => 'required|string|max:255',
            'telefono'   => [
                'required',
                'string',
                'max:50',
                // Validación única de teléfono restringida a la misma empresa
                function ($attribute, $value, $fail) use ($user) {
                    $exists = DB::table('clientes')
                        ->where('telefono', $value)
                        ->when(!empty($user->company_id), function($q) use ($user) {
                            $q->where('company_id', $user->company_id);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('Este número de teléfono ya se encuentra registrado en el sistema de su empresa.');
                    }
                },
            ],
            'correo'     => 'nullable|email|max:255',
            'empresa'    => 'nullable|string|max:255',
            'direccion'  => 'nullable|string|max:255',
            'estado_crm' => 'required|string|max:50',
        ]);

        DB::table('clientes')->insert([
            'company_id' => $user->company_id ?? null,
            'nombre'     => $request->nombre,
            'telefono'   => $request->telefono,
            'correo'     => $request->correo,
            'empresa'    => $request->empresa,
            'direccion'  => $request->direccion,
            'estado_crm' => $request->estado_crm,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Cliente registrado correctamente.');
    }

    // Actualizar los datos principales del cliente
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $query = DB::table('clientes')->where('id', $id);
        if (!empty($user->company_id)) {
            $query->where('company_id', $user->company_id);
        }

        $query->update([
            'nombre'     => $request->nombre,
            'correo'     => $request->correo,
            'empresa'    => $request->empresa,
            'direccion'  => $request->direccion,
            'estado_crm' => $request->estado_crm,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Ficha de cliente actualizada correctamente.');
    }

    // Guardar una nueva nota en el timeline
    public function storeNota(Request $request, $id)
    {
        $request->validate(['nota' => 'required|string']);

        DB::table('cliente_notas')->insert([
            'cliente_id'    => $id,
            'user_id'       => Auth::id() ?? null,
            'nota'          => $request->nota,
            'call_uniqueid' => $request->call_uniqueid ?? null,
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        return back()->with('success', 'Nota registrada con éxito en el historial.');
    }
    
    // Exportar clientes a CSV/Excel de la empresa actual
    public function export()
    {
        $user = Auth::user();
        $fileName = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';
        
        $query = DB::table('clientes');
        if (!empty($user->company_id)) {
            $query->where('company_id', $user->company_id);
        }
        $clientes = $query->get();
    
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
    
        $columns = ['Nombre', 'Teléfono', 'Correo', 'Empresa', 'Dirección', 'Estado CRM'];
    
        $callback = function() use($clientes, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fnputcsv($file, $columns);
    
            foreach ($clientes as $cli) {
                fputcsv($file, [
                    $cli->nombre,
                    $cli->telefono,
                    $cli->correo,
                    $cli->empresa,
                    $cli->direccion,
                    $cli->estado_crm
                ]);
            }
            fclose($file);
        };
    
        return response()->stream($callback, 200, $headers);
    }
    
    // Importar clientes desde un archivo CSV/Excel asignándoles su empresa
    public function import(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'archivo_excel' => 'required|file|mimes:csv,txt,xlsx,xls'
        ]);
    
        $file = $request->file('archivo_excel');
        $path = $file->getRealPath();
        
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data);
    
        $importados = 0;
    
        foreach ($data as $row) {
            if (count($row) >= 2 && !empty($row[1])) {
                $telefono = trim($row[1]);
                
                // Actualiza o inserta evaluando tanto el teléfono como el company_id para no mezclar empresas
                DB::table('clientes')->updateOrInsert(
                    [
                        'telefono'   => $telefono,
                        'company_id' => $user->company_id ?? null
                    ],
                    [
                        'nombre'     => trim($row[0] ?? 'Sin Nombre'),
                        'correo'     => trim($row[2] ?? null),
                        'empresa'    => trim($row[3] ?? null),
                        'direccion'  => trim($row[4] ?? null),
                        'estado_crm' => trim($row[5] ?? 'Prospecto'),
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
                $importados++;
            }
        }
    
        return back()->with('success', "Se importaron / actualizaron $importados clientes correctamente.");
    }   
}