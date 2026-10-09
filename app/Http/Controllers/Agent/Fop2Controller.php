<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Fop2Controller extends Controller
{
    private function fetchCallsFromApi(string $fechaInicio, string $fechaFin, array $extensionsList = []): array
    {
        $extensionsString = !empty($extensionsList) ? implode(',', $extensionsList) : 'all';
    
        $response = \Illuminate\Support\Facades\Http::get("https://servidores.voiparkas.com/cdrweb/llamacrm/api_cdr_acall.php", [
            'extension'    => $extensionsString,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin
        ]);
    
        if ($response->successful()) {
            $data = $response->json();
            return $data['llamadas'] ?? [];
        }
    
        return [];
    }

    private function isOutboundCall(array $call, array $extensionsList): bool
    {
        $src = trim($call['src'] ?? '');
        $channel = trim($call['channel'] ?? '');

        foreach ($extensionsList as $ext) {
            if ($src === $ext || str_contains($channel, "SIP/{$ext}-") || str_contains($channel, "PJSIP/{$ext}-")) {
                return true;
            }
        }
        return false;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? 'agent';
        $company = $user->company ?? null;
        $isSuperAdmin = in_array($userRole, ['superadmin', 'super_admin']);

        // Si no es superadmin y tiene compañía, calculamos y actualizamos el estatus en la BD
        if (!$isSuperAdmin && $company) {
            
            // 1. Minutos totales contratados/recargados
            $minutosPaqueteTotal = DB::table('company_minute_logs')
                ->where('company_id', $company->id)
                ->sum('minutes_added');

            if ($minutosPaqueteTotal == 0 && isset($company->minutes_limit)) {
                $minutosPaqueteTotal = $company->minutes_limit;
            }

            // 2. Extensiones de la compañía
            $userIds = DB::table('users')->where('company_id', $company->id)->pluck('id');
            $extensionsList = DB::table('user_extensions')
                ->whereIn('user_id', $userIds)
                ->pluck('extension_number')
                ->toArray();

            // 3. Fechas para conteo
            $fechaInicioHistorica = !empty($company->created_at) ? Carbon::parse($company->created_at)->format('Y-m-d') : Carbon::now('America/Bogota')->startOfMonth()->format('Y-m-d');
            $todayStr = Carbon::today('America/Bogota')->format('Y-m-d');

            // 4. Conteo de llamadas consumidas válidas
            $totalMinutosConsumidos = 0;
            if (!empty($extensionsList)) {
                $rawCallsHistoricas = $this->fetchCallsFromApi($fechaInicioHistorica, $todayStr, $extensionsList);
                
                foreach ($rawCallsHistoricas as $call) {
                    $src = trim($call['src'] ?? '');
                    $dst = trim($call['dst'] ?? '');
                    $disposition = strtoupper(trim($call['disposition'] ?? ''));

                    if ($disposition !== 'ANSWERED') {
                        continue;
                    }

                    if (strlen($src) <= 4 && strlen($dst) <= 4) {
                        continue; // Descartar internas
                    }

                    if ($this->isOutboundCall($call, $extensionsList)) {
                        $totalMinutosConsumidos++;
                    }
                }
            }

            // 5. Comparar y actualizar el estatus en la tabla user_extensions
            $minutosDisponibles = $minutosPaqueteTotal - $totalMinutosConsumidos;

            if ($minutosDisponibles <= 0) {
                // Si se acabaron, guardamos 'inactive'
                DB::table('user_extensions')
                    ->whereIn('user_id', $userIds)
                    ->update(['status' => 'inactive']);
            } else {
                // Si hay saldo, aseguramos 'active'
                DB::table('user_extensions')
                    ->whereIn('user_id', $userIds)
                    ->update(['status' => 'active']);
            }
        }

        // Obtenemos los datos de la extensión del usuario actual
        $extensionRecord = $user->activeExtension ?? $user->extensions()->first();
        $extension = $extensionRecord ? $extensionRecord->extension_number : '';
        $pass = $extensionRecord ? $extensionRecord->sip_secret : '';

        // Pasamos todo a la vista (incluyendo el registro de la extensión para leer su status)
        return view('agent.fop2.index', compact('user', 'extension', 'pass', 'extensionRecord'));
    }
}