<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Filtramos para que solo traiga los usuarios de la misma compañía del usuario autenticado
        $users = User::where('company_id', auth()->user()->company_id)
                     ->with(['activeExtension', 'sessions'])
                     ->get();
        
        // Obtenemos los servidores SIP disponibles para el selector
        $sipServers = \App\Models\SipServer::where('is_active', 1)->get();
    
        return view('agent.users.index', compact('users', 'sipServers'));
    }

    public function create()
    {
        return view('agent.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([

            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users',
            'password'         => 'required|string|min:6',
            'role'             => 'required|in:super_admin,admin,supervisor,agent',
            'status'           => 'required|in:active,inactive',
            'extension_number' => 'nullable|string|max:50|unique:user_extensions,extension_number',
            'sip_secret'       => 'nullable|string|max:255',
            'websocket_url'    => 'nullable|string|max:255',
        ]);
    
        DB::transaction(function () use ($request) {
            $user = User::create([
                'company_id' => auth()->user()->company_id, 
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'status'   => $request->status,
            ]);
    
            // Solo crea la extensión si se escribió un número
            if ($request->filled('extension_number')) {
                UserExtension::create([
                    'user_id'          => $user->id,
                    'extension_number' => $request->extension_number,
                    'sip_secret'       => $request->sip_secret ?? '1234',
                    'websocket_url'    => $request->websocket_url ?? 'wss://servidores.voiparkas.com:8089/ws',
                ]);
            }
        });
    
        return redirect()->route('agent.users.index')->with('success', 'Usuario registrado correctamente.');
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'             => 'required|in:agent,supervisor,admin',
            'status'           => 'required|in:active,inactive',
            'extension_number' => 'nullable|string|max:50',
            'sip_secret'       => 'nullable|string|max:255',
            'websocket_url'    => 'nullable|string|max:255',
        ]);
    
        // Actualizamos los datos del usuario (SIN tocar el company_id)
        $user->update([
            'name'   => $request->name,
            'email'  => $request->email,
            'role'   => $request->role,
            'status' => $request->status,
        ]);
    
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }
    
        // Gestión de la extensión SIP vinculada
        if ($request->filled('extension_number')) {
            $extensionData = [
                'extension_number' => $request->extension_number,
                'websocket_url'    => $request->websocket_url,
            ];

            // Solo actualizar el secreto SIP si el usuario escribió uno nuevo en el modal
            if ($request->filled('sip_secret')) {
                $extensionData['sip_secret'] = $request->sip_secret;
            }

            UserExtension::updateOrCreate(
                ['user_id' => $user->id],
                $extensionData
            );
        }
    
        return redirect()->route('agent.users.index')->with('success', 'Usuario actualizado correctamente.');
    }
    
    public function destroy(User $user)
    {
        // Evitar que el usuario autenticado se elimine a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('agent.users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Verificar que pertenezca a la misma compañía (seguridad multi-tenant)
        if ($user->company_id !== auth()->user()->company_id) {
            abort(403, 'Acción no autorizada.');
        }

        DB::transaction(function () use ($user) {
            // Eliminar primero la extensión SIP asociada si existe
            UserExtension::where('user_id', $user->id)->delete();

            // Eliminar el usuario
            $user->delete();
        });

        return redirect()->route('agent.users.index')->with('success', 'Usuario eliminado correctamente.');
    }    
}