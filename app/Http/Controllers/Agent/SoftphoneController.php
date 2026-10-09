<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class SoftphoneController extends Controller
{
    /**
     * Genera un token temporal y retorna la URL del marcador externo para el agente.
     */
    public function AnteriormarcadorLibre()
    {
        $user = Auth::user();

        // Validar que sea un agente
        if (!$user || $user->role !== 'agent') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Buscar la extensión y secreto SIP en la base de datos local
        $userExtension = DB::table('user_extensions')
            ->where('user_id', $user->id)
            ->first();

        if (!$userExtension) {
            return response()->json(['error' => 'El agente no tiene una extensión SIP asignada.'], 422);
        }

        // Crear un token temporal único (válido por 5 minutos)
        $token = 'acall_' . Str::random(40);

        // Guardar en caché los datos que el marcador necesita
        Cache::put('voip_token_' . $token, [
            'extension' => $userExtension->extension_number,
            'secret' => $userExtension->sip_secret
        ], now()->addMinutes(5));

        // URL de tu marcador externo
        $urlMarcador = "https://servidores.voiparkas.com/cdrweb/llamacrm/dialeracall/marcador.php?token=" . $token;

        return response()->json([
            'status' => 'success',
            'url_marcador' => $urlMarcador
        ]);
    }

    public function marcadorLibre()
    {
        $user = Auth::user();

        // Validar que esté autenticado (permitiendo agilizar pruebas si eres admin o agent)
        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Buscar la extensión y secreto SIP en la base de datos local
        $userExtension = DB::table('user_extensions')
            ->where('user_id', $user->id)
            ->first();

        if (!$userExtension) {
            return response()->json([
                'status' => 'error',
                'error' => 'El agente no tiene una extensión SIP asignada en user_extensions.'
            ], 422);
        }

        // Retornamos directamente lo que JsSIP necesita para registrarse
        return response()->json([
            'status' => 'success',
            'extension' => $userExtension->extension_number,
            'secret' => $userExtension->asterisk_secret
        ]);
    }
    /**
     * Responde a la petición cURL del servidor externo para entregar la extensión y contraseña.
     */
    public function resolverToken(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 400);
        }

        // Recuperar los datos guardados en caché
        $sipData = Cache::get('voip_token_' . $token);

        if (!$sipData) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }

        // Retornar los datos en formato JSON
        return response()->json($sipData);
    }
    

    public function recibirMensaje(Request $request)
    {
        // Datos que enviará Asterisk
        $remitente = $request->input('from');
        $destinatario = $request->input('to');
        $mensaje = $request->input('body');
    
        if (!$remitente || !$mensaje) {
            return response()->json(['error' => 'Datos incompletos'], 400);
        }
    
        // Guardar en tu base de datos (ej: tabla messages)
        DB::table('messages')->insert([
            'sender' => $remitente,
            'recipient' => $destinatario,
            'body' => $mensaje,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return response()->json(['status' => 'success']);
    }    
}