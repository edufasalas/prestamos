<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserExtensionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Obtenemos la extensión actual del usuario logueado
        $extension = UserExtension::where('user_id', $user->id)->first();
        
        // Variable necesaria para que el aside muestre la extensión en línea
        $sipExtension = $extension ? $extension->extension_number : 'N/A';

        return view('agent.extensions.index', compact('extension', 'sipExtension'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'extension_number' => 'required|string|max:50',
            'sip_secret' => 'required|string|max:255',
            'websocket_url' => 'required|string|max:255',
        ]);

        // Crea o actualiza la extensión vinculada al usuario autenticado
        UserExtension::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'extension_number' => $request->extension_number,
                'sip_secret' => $request->sip_secret,
                'websocket_url' => $request->websocket_url,
            ]
        );

        return redirect()->back()->with('success', 'La extensión SIP se ha guardado y configurado correctamente.');
    }
}