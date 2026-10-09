<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExtensionController extends Controller
{
    public function create()
    {
        // Validación de rol del administrador
        if (!in_array(auth()->user()->role ?? '', ['super_admin', 'admin'])) {
            // Si manejas tu autenticación con otro campo, ajústalo aquí
            // abort(403, 'Acceso denegado.');
        }

        // 1. Traemos los usuarios de la base de datos local
        $users = \DB::table('users')->select('id', 'name')->get();

        // 2. Los pasamos a la vista
        return view('admin.extensions.create', compact('users'));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'extension'   => 'required|string|max:10',
            'name'        => 'required|string|max:50',
            'accountcode' => 'required|string|max:20',
            'password'    => 'required|string|min:4',
            'tipo'        => 'required|in:sip,webrtc,pjsip',
        ]);
    
        try {
            // 1. Guardar o actualizar el registro en la tabla local `user_extensions`
            // Usamos el ID del usuario autenticado actualmente (o un ID por defecto si prefieres)
            $userId = auth()->id() ?? 1; 

            \DB::table('user_extensions')->updateOrInsert(
                ['extension_number' => $request->extension],
                [
                    'user_id'       => $userId,
                    'sip_secret'    => $request->password,
                    'websocket_url' => 'pbx.voiparkas.com',
                    'status'        => 'active',
                    'updated_at'    => now(),
                    'created_at'    => now(), // Se asigna automáticamente si es nuevo, o puedes omitirlo
                ]
            );

            // 2. Petición a la API de Issabel
            $apiCreateUrl = 'https://servidores.voiparkas.com/cdrweb/llamacrm/api_crear_extension.php';
        
            $response = Http::asForm()->timeout(15)->post($apiCreateUrl, $request->all());
            
            // Si Issabel responde HTTP 200, el script terminó su ejecución y guardó en BD
            if ($response->successful()) {
                $body = trim($response->body());
                
                // Si viene vacío (que es lo que pasa siempre por culpa del bug de Issabel)
                if (empty($body) || $body === '[]') {
                    return response()->json([
                        'status' => 'success',
                        'mensaje' => "Extensión {$request->extension} creada localmente y registrada con éxito en Issabel."
                    ]);
                }
    
                // Si por milagro llega un JSON real, lo procesamos normal
                $json = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($json['status'])) {
                    return response()->json($json);
                }
    
                // Fallback general de éxito si hubo respuesta pero no fue JSON válido
                return response()->json([
                    'status' => 'success',
                    'mensaje' => "Extensión {$request->extension} registrada localmente y en el core correctamente."
                ]);
            }
    
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Error de comunicación con el servidor Issabel (Código: ' . $response->status() . ')'
            ], 500);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Excepción de red o base de datos: ' . $e->getMessage()
            ], 500);
        }
    }
    public function reload(Request $request)
    {
        try {
            $apiReloadUrl = 'https://servidores.voiparkas.com/cdrweb/llamacrm/api_reload_asterisk.php';

            // Subimos el timeout a 30 segundos para darle tiempo a Asterisk
            $response = Http::timeout(30)->post($apiReloadUrl);

            // Si Issabel responde HTTP 200, la recarga se ejecutó en el servidor
            if ($response->successful()) {
                $body = trim($response->body());
                $data = $response->json();

                // Si viene vacío o es un array vacío pero el código fue 200 (éxito por defecto)
                if (empty($body) || $body === '[]' || is_null($data)) {
                    return response()->json([
                        'status' => 'ok',
                        'mensaje' => 'Cambios aplicados correctamente en Asterisk.'
                    ]);
                }
    
                // Si sí devolvió JSON válido
                if (isset($data['status']) && ($data['status'] === 'ok' || $data['status'] === 'success')) {
                    return response()->json([
                        'status' => 'ok',
                        'mensaje' => $data['mensaje'] ?? 'Cambios aplicados correctamente en Asterisk.'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'mensaje' => $data['mensaje'] ?? 'Error reportado al recargar Asterisk.'
                    ], 422);
                }
            }

            return response()->json([
                'status' => 'error',
                'mensaje' => 'Fallo al comunicarse con el script de recarga (Código: ' . $response->status() . ')'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Excepción de red al recargar Asterisk: ' . $e->getMessage()
            ], 500);
        }
    }
}