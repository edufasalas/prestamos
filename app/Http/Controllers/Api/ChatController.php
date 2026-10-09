<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    // TU MÉTODO ORIGINAL INTACTO Y FUNCIONAL PARA ENTRANTES
    public function storeIncoming(Request $request)
    {
        $from = $request->input('from', 'unknown');
        $to = $request->input('to', 'system');
        $body = $request->input('body', 'empty');
        $channel = $request->input('channel', 'sip');
    
        // Extraer solo los números de la extensión receptora (ej. "7007" de <sip:7007@...>)
        preg_match('/(\d+)/', $to, $matches);
        $extNumber = $matches[1] ?? trim($to);
    
        // Buscar el user_id exacto en user_extensions
        $userId = null;
        $userExt = DB::table('user_extensions')
            ->where('extension_number', $extNumber)
            ->first();
    
        if ($userExt) {
            $userId = $userExt->user_id;
        }
    
        DB::table('messages')->insert([
            'from_contact' => $from,
            'to_contact' => $to,
            'body' => $body,
            'direction' => 'incoming',
            'channel' => $channel,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return response()->json(['status' => 'success', 'user_id_saved' => $userId]);
    }

    // Historial simple en formato array que ya funcionaba
    public function getHistory(Request $request, $extension)
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return response()->json([]);
        }

        // 1. Obtenemos las extensiones del usuario actual (tú)
        $myExtensions = DB::table('user_extensions')
            ->where('user_id', $currentUser->id)
            ->pluck('extension_number')
            ->toArray();

        if (empty($myExtensions)) {
            return response()->json([]);
        }

        // 2. Buscamos SOLAMENTE los mensajes donde interactúan TUS extensiones y la del contacto seleccionado ($extension)
        $messages = DB::table('messages')
            ->where(function($query) use ($myExtensions, $extension) {
                // Caso A: Tú enviaste (from = tu ext) y el otro recibió (to = ext seleccionada)
                foreach ($myExtensions as $myExt) {
                    $query->orWhere(function($q) use ($myExt, $extension) {
                        $q->where('from_contact', 'LIKE', "%{$myExt}%")
                          ->where('to_contact', 'LIKE', "%{$extension}%");
                    });
                }
                // Caso B: El otro envió (from = ext seleccionada) y tú recibiste (to = tu ext)
                foreach ($myExtensions as $myExt) {
                    $query->orWhere(function($q) use ($myExt, $extension) {
                        $q->where('from_contact', 'LIKE', "%{$extension}%")
                          ->where('to_contact', 'LIKE', "%{$myExt}%");
                    });
                }
            })
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        // 3. Mapeamos la dirección correctamente respecto a ti
        $messages->transform(function ($msg) use ($myExtensions) {
            $isMine = false;
            foreach ($myExtensions as $myExt) {
                if (str_contains($msg->from_contact, $myExt)) {
                    $isMine = true;
                    break;
                }
            }
            $msg->direction = $isMine ? 'outgoing' : 'incoming';
            return $msg;
        });

        return response()->json($messages);
    }

    // Envío saliente original
    public function sendOutgoing(Request $request)
    {
        $request->validate([
            'to_contact' => 'required',
            'body'       => 'nullable|string',
            'file'       => 'nullable|file|max:10240',
        ]);

        $body = $request->input('body');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Limpiamos espacios, paréntesis y caracteres especiales por guiones bajos (_), dejando solo letras, números y la extensión
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
            $filename = time() . '_' . $cleanName . '.' . $extension;
            
            $destinationPath = public_path('uploads/chat_uploads');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $body = url('public/uploads/chat_uploads/' . $filename);
        }

        $myExtension = DB::table('user_extensions')
            ->where('user_id', auth()->id())
            ->value('extension_number');

        if (!$myExtension) {
            $myExtension = auth()->user()->name ?? 'WebPanel';
        }

        DB::table('messages')->insert([
            'from_contact' => $myExtension,
            'to_contact'   => $request->input('to_contact'),
            'body'         => $body ?? '',
            'direction'    => 'outgoing',
            'channel'      => 'sip',
            'user_id'      => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    // Método auxiliar para contactos
    public function getContacts(Request $request)
    {
        $currentUser = auth()->user();

        $contacts = DB::table('user_extensions')
            ->join('users', 'user_extensions.user_id', '=', 'users.id')
            ->where('users.id', '!=', $currentUser->id)
            ->where('users.company_id', '=', $currentUser->company_id)
            ->select('users.name as agent_name', 'user_extensions.extension_number')
            ->get();

        return response()->json($contacts);
    }
    
    public function checkUnread(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return response()->json(['has_new' => false]);
        }
    
        $extensions = DB::table('user_extensions')
            ->where('user_id', $currentUser->id)
            ->pluck('extension_number')
            ->toArray();
    
        if (empty($extensions)) {
            return response()->json(['has_new' => false]);
        }
    
        // Buscamos si hay mensajes dirigidos a sus extensiones en los últimos 30 segundos
        // (Esto evita que se quede pegado al infinito y apaga el aviso automáticamente si pasa el tiempo)
        $hasNew = DB::table('messages')
            ->where(function($query) use ($extensions) {
                foreach ($extensions as $ext) {
                    $query->orWhere('to_contact', 'LIKE', "%{$ext}%");
                }
            })
            ->where(function($query) use ($extensions) {
                foreach ($extensions as $ext) {
                    $query->where('from_contact', 'NOT LIKE', "%{$ext}%");
                }
            })
            ->where('created_at', '>=', now()->subSeconds(30)) // Solo cuenta los de los últimos 30 segundos
            ->exists();
    
        return response()->json(['has_new' => $hasNew]);
    }

    public function markAsRead(Request $request)
    {
        // No hace falta guardar nada en caché ni sesiones que rompan el servidor. 
        // Con esto la ruta responde OK y la UI del navegador se encarga de apagar el botón al hacer clic.
        return response()->json(['success' => true]);
    }   
}