<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunCampaignDialer extends Command
{
    protected $signature = 'dialer:run';
    protected $description = 'Procesa las campañas pendientes y dispara las llamadas vía API AMI hacia Issabel';

    public function handle()
    {
        $this->info('Iniciando motor de discado masivo/predictivo...');

        // 1. Buscar campañas activas/running
        $campaigns = DB::table('campaigns')
            ->where('status', 'running')
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No hay campañas activas en este momento.');
            return;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Procesando campaña: {$campaign->name} (ID: {$campaign->id})");

            // 2. Obtener los leads pendientes limitados por los canales máximos de la campaña
            $pendingLeads = DB::table('campaign_clientes')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->limit($campaign->max_channels)
                ->get();

            if ($pendingLeads->isEmpty()) {
                // Si ya no quedan pendientes, marcamos la campaña como finalizada
                DB::table('campaigns')->where('id', $campaign->id)->update(['status' => 'finished']);
                continue;
            }

            foreach ($pendingLeads as $lead) {
                // Marcamos como 'dialing' para evitar que otro ciclo lo vuelva a tomar
                DB::table('campaign_clientes')->where('id', $lead->id)->update([
                    'status' => 'dialing',
                    'tries' => DB::raw('tries + 1'),
                    'last_dialed_at' => now()
                ]);

                // 3. Petición HTTP al API de Issabel que configuramos
                try {
                    $response = Http::timeout(10)->post('https://servidores.voiparkas.com/cdrweb/llamacrm/api_dialer_trigger.php', [
                        'token'         => 'tu_token_secreto_compartido_laravel_issabel', // Debe coincidir con el del script en Issabel
                        'phone'         => $lead->phone,
                        'trunk'         => $campaign->trunk,
                        'campaign_type' => $campaign->campaign_type,
                        'queue_number'  => $campaign->queue_number,
                        'audio_file'    => $campaign->audio_file,
                    ]);

                    $resData = $response->json();

                    if ($resData && isset($resData['status']) && $resData['status'] === 'success') {
                        DB::table('campaign_clientes')->where('id', $lead->id)->update(['status' => 'connected']);
                        $this->info(" [✔] Llamada exitosa al número: {$lead->phone}");
                    } else {
                        DB::table('campaign_clientes')->where('id', $lead->id)->update(['status' => 'failed']);
                        $this->error(" [✖] Error al llamar a {$lead->phone}: " . ($resData['message'] ?? 'Respuesta inválida'));
                    }

                } catch (\Exception $e) {
                    DB::table('campaign_clientes')->where('id', $lead->id)->update(['status' => 'failed']);
                    Log::error("Excepción al conectar con Issabel para el lead {$lead->phone}: " . $e->getMessage());
                    $this->error(" [✖] Excepción de red/API para {$lead->phone}");
                }
            }
        }

        $this->info('Ciclo de discado finalizado.');
    }
}