<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class CampaignController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
    
        // Obtenemos las campañas de la compañía
        $campaigns = DB::table('campaigns')
                        ->where('company_id', $companyId)
                        ->orderBy('id', 'desc')
                        ->get();
    
        return view('agent.campaigns.index', compact('campaigns'));
    }   

 

    /**
     * Inicia la campaña manualmente al hacer clic en el botón
     */
public function start($id)
{
    try {
        // Actualización directa y segura por DB
        $updated = DB::table('campaigns')
            ->where('id', $id)
            ->update([
                'status' => 'running',
                'updated_at' => now()
            ]);

        if (!$updated) {
            return back()->withErrors(['error' => 'No se pudo actualizar la campaña.']);
        }

        return back()->with('success', '¡Campaña iniciada correctamente!');
        
    } catch (\Exception $e) {
        // Esto evita el Error 500 y te muestra exactamente qué falló si hay un problema en la BD
        return back()->withErrors(['error' => 'Excepción: ' . $e->getMessage()]);
    }
}

public function stop($id)
{
    try {
        $updated = DB::table('campaigns')
            ->where('id', $id)
            ->update([
                'status' => 'paused', // Usamos 'paused' porque está en tu ENUM real
                'updated_at' => now()
            ]);

        if (!$updated) {
            return back()->withErrors(['error' => 'No se pudo detener la campaña.']);
        }

        return back()->with('success', '¡Campaña detenida correctamente!');
        
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Excepción: ' . $e->getMessage()]);
    }
}
   
    /**
     * Almacena una nueva campaña y procesa la lista de clientes (CSV)
     */
    public function store(Request $request)
    {
        // 1. Validamos los datos (sin pedir extensión en el form)
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'campaign_type'     => 'required|in:queue,broadcast',
            'queue_number'      => 'required_if:campaign_type,queue|nullable|string|max:50',
            'max_channels'      => 'required|integer|min:1|max:50',
            'audio_file_upload' => 'nullable|file|mimes:wav,mp3',
            'intro_audio_upload'=> 'nullable|file|mimes:wav,mp3',
            'requires_dtmf'     => 'sometimes|boolean',
            'leads_file'        => 'required|file|mimes:csv,txt',
            'company_id'        => 'required'
        ]);
    
        try {
            DB::beginTransaction();
    
            // 2. Buscamos la extensión del usuario autenticado en la tabla UserExtension
            $userExtensionRecord = \App\Models\UserExtension::where('user_id', Auth::id())->first();
    
            if (!$userExtensionRecord || empty($userExtensionRecord->extension_number)) {
                return redirect()->back()->withErrors(['error' => 'Primero debes configurar y guardar tu extensión SIP en tu perfil antes de crear una campaña.'])->withInput();
            }
    
            $userExtension = $userExtensionRecord->extension_number;
    
            // Procesar subida de audio para Broadcast si viene incluido
            $audioFilepath = null;
            if ($request->hasFile('audio_file_upload')) {
                $file = $request->file('audio_file_upload');
                $filename = 'broadcast_' . time() . '_' . rand(100, 999);
                
                if (!file_exists(public_path('storage/custom_sounds'))) {
                    mkdir(public_path('storage/custom_sounds'), 0777, true);
                }
                
                $file->move(public_path('storage/custom_sounds'), $filename . '.' . $file->getClientOriginalExtension());
                $audioFilepath = 'custom/' . $filename;
            }
    
            // Procesar subida de audio de Introducción si viene incluido
            $introAudioFilepath = null;
            if ($request->hasFile('intro_audio_upload')) {
                $file = $request->file('intro_audio_upload');
                $filename = 'intro_' . time() . '_' . rand(100, 999);
                
                if (!file_exists(public_path('storage/custom_sounds'))) {
                    mkdir(public_path('storage/custom_sounds'), 0777, true);
                }
    
                $file->move(public_path('storage/custom_sounds'), $filename . '.' . $file->getClientOriginalExtension());
                $introAudioFilepath = 'custom/' . $filename;
            }
    
            // 3. Crear la campaña usando el 'extension_number' correcto
            $campaignId = DB::table('campaigns')->insertGetId([
                'company_id'    => $validated['company_id'],
                'name'          => $validated['name'],
                'campaign_type' => $validated['campaign_type'],
                'queue_number'  => $validated['queue_number'] ?? null,
                'extension'     => $userExtension, // <-- Aquí va el número de extensión real de UserExtension
                'max_channels'  => $validated['max_channels'],
                'audio_file'    => $audioFilepath,
                'intro_audio'   => $introAudioFilepath,
                'requires_dtmf' => $request->has('requires_dtmf') ? 1 : 0,
                'status'        => 'pending',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
    
            // 4. Procesar el archivo CSV de clientes/leads
            if ($request->hasFile('leads_file')) {
                $file = $request->file('leads_file');
                $path = $file->getRealPath();
                
                $handle = fopen($path, 'r');
                $isFirstRow = true;
    
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        if (!empty($row[1]) && !is_numeric(preg_replace('/[^0-9]/', '', $row[1]))) {
                            continue;
                        }
                    }
    
                    $nombre   = trim($row[0] ?? 'Cliente S/N');
                    $telefono = trim($row[1] ?? $row[0]);
    
                    if (empty($telefono)) continue;
    
                    $clienteId = DB::table('clientes')
                        ->where('telefono', $telefono)
                        ->where('company_id', $validated['company_id'])
                        ->value('id');
    
                    if (!$clienteId) {
                        $clienteId = DB::table('clientes')->insertGetId([
                            'company_id' => $validated['company_id'],
                            'nombre'     => $nombre,
                            'telefono'   => $telefono,
                            'estado_crm' => 'Prospecto',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
    
                    DB::table('campaign_clientes')->insert([
                        'campaign_id' => $campaignId,
                        'cliente_id'  => $clienteId,
                        'phone'       => $telefono,
                        'status'      => 'pending',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
                fclose($handle);
            }
    
            DB::commit();
    
            return redirect()->back()->with('success', '¡Campaña creada y leads importados con éxito!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en store de campañas: ' . $e->getMessage() . ' en línea ' . $e->getLine());
            return redirect()->back()->withErrors(['error' => 'Error al procesar la campaña: ' . $e->getMessage()])->withInput();
        }
    }
}