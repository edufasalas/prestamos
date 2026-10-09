<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CompanyPbxController extends Controller
{
    /**
     * Lógica probada y exacta para obtener las extensiones permitidas por empresa/rol.
     */
    private function getAllowedExtensions($user): array
    {
        if ($user->role === 'superadmin' || $user->role === 'super_admin') {
            $allCompanyUserIds = DB::table('users')->whereNotNull('company_id')->pluck('id');
            
            return DB::table('user_extensions')
                     ->whereIn('user_id', $allCompanyUserIds)
                     ->pluck('extension_number')
                     ->toArray();
        }
        
        if (in_array($user->role, ['admin', 'supervisor'])) {
            $userIds = DB::table('users')->where('company_id', $user->company_id)->pluck('id');
            
            return DB::table('user_extensions')
                     ->whereIn('user_id', $userIds)
                     ->pluck('extension_number')
                     ->toArray();
        }
    
        return DB::table('user_extensions')
                 ->where('user_id', $user->id)
                 ->pluck('extension_number')
                 ->toArray();
    }

    /**
     * Muestra la vista de PBX de la empresa logueada
     */
    public function index()
    {
        $user = Auth::user();
        
        $company = Company::findOrFail($user->company_id);

        return view('admin.pbx.companypbx', compact('company'));
    }
    /**
     * Muestra la vista de creación y configuración de IVR / DID PBX
     */
    public function creacion()
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);

        return view('admin.pbx.creacionpbx', compact('company'));
    }
    /**
     * Consulta en tiempo real el estado de las extensiones utilizando la lógica de filtrado probada
     */
    public function getRealtimeStatus()
    {
        $user = Auth::user();
        
        $company = Company::findOrFail($user->company_id);

        // Obtenemos los números de extensión con el método seguro que ya funciona en tu app
        $extensionNumbers = $this->getAllowedExtensions($user);

        try {
            // Petición HTTP al script API de Issabel
            $response = Http::timeout(5)->get('https://servidores.voiparkas.com/cdrweb/llamacrm/api_estado_pbx.php');

            if ($response->successful()) {
                $data = $response->json();
                $allExtensions = $data['extensions'] ?? [];
                
                // AQUÍ ESTÁ EL CAMBIO: Capturamos el DID activo que manda Issabel
                $activeDidCall = $data['active_did_call'] ?? null;

                // Cruzamos los datos asegurándonos de mostrar únicamente los de su compañía
                $companyExtensionsStatus = [];
                foreach ($extensionNumbers as $extNum) {
                    if (isset($allExtensions[$extNum])) {
                        $companyExtensionsStatus[$extNum] = $allExtensions[$extNum];
                    } else {
                        $companyExtensionsStatus[$extNum] = [
                            'extension' => $extNum,
                            'status'    => 'offline',
                            'raw_detail'=> 'Sin registro activo'
                        ];
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'company' => [
                        'name' => $company->name,
                        'incoming_did' => $company->incoming_did,
                        'max_concurrent_calls' => $company->max_concurrent_calls,
                        'has_ivr' => $company->has_ivr,
                    ],
                    'extensions' => $companyExtensionsStatus,
                    'active_did_call' => $activeDidCall // <-- Y lo reenviamos aquí a tu Alpine.js
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo comunicar con el motor de telefonía.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ejecuta acciones de control (espiar o colgar) enviando la orden a la API remota de Issabel
     */
    public function controlAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:hangup',
            'extension' => 'required|string',
        ]);

        try {
            // Petición limpia a la API remota solo con la acción y la extensión objetivo
            $response = Http::timeout(5)->asForm()->post('https://servidores.voiparkas.com/cdrweb/llamacrm/api_estado_pbx.php', [
                'action' => $request->input('action'),
                'extension' => $request->input('extension')
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo comunicar con el servidor de telefonía.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    } 
    
    public function storeIvrDid(Request $request)
    {
        // 1. Validar exactamente lo que envía el formulario Alpine.js
        $request->validate([
            'did' => 'required|string',
            'ivr_name' => 'required|string|max:100',
            'recording_file' => 'nullable|file|mimes:wav,mp3,gsm|max:10240',
            'ivr_options' => 'required|string', // El JSON con las opciones del menú
        ]);
    
        try {
            $http = Http::timeout(60)->withoutVerifying();
    
            // 2. Si hay archivo de audio adjunto, lo vinculamos con multipart
            if ($request->hasFile('recording_file')) {
                $file = $request->file('recording_file');
                $http->attach(
                    'recording_file', 
                    file_get_contents($file->path()), 
                    $file->getClientOriginalName()
                );
            }
    
            // 3. Enviamos los campos de texto correctos al servidor remoto de Issabel
            $response = $http->post('https://servidores.voiparkas.com/cdrweb/llamacrm/api_procesar_ivr_did.php', [
                'did' => $request->input('did'),
                'ivr_name' => $request->input('ivr_name'),
                'ivr_options' => $request->input('ivr_options'),
            ]);
    
            // 4. Evaluar la respuesta del servidor de telefonía
            if ($response->successful()) {
                $json = $response->json();
                if ($json && isset($json['ok']) && $json['ok']) {
                    return response()->json($json);
                }
                return response()->json([
                    'ok' => false,
                    'msg' => isset($json['msg']) ? $json['msg'] : 'Error desconocido en Issabel'
                ], 500);
            }
    
            return response()->json([
                'ok' => false,
                'msg' => 'No se pudo procesar la solicitud en el servidor de telefonía. Status: ' . $response->status()
            ], 500);
    
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }  
}