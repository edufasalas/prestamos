<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function fetchCallsFromApi(string $fechaInicio, string $fechaFin, array $extensionsList = []): array
    {
        $extensionsString = !empty($extensionsList) ? implode(',', $extensionsList) : 'all';
    
        $response = Http::get("https://servidores.voiparkas.com/cdrweb/llamacrm/api_cdr_acall.php", [
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

    private function getAllowedExtensions($user): array
    {
        $role = $user->role ?? 'agent';

        if (in_array($role, ['superadmin', 'super_admin'])) {
            $allCompanyUserIds = DB::table('users')->whereNotNull('company_id')->pluck('id');
            return DB::table('user_extensions')
                     ->whereIn('user_id', $allCompanyUserIds)
                     ->pluck('extension_number')
                     ->toArray();
        }
        
        if (in_array($role, ['admin', 'supervisor'])) {
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

    private function callBelongsToExtensions(array $call, array $extensionsList): bool
    {
        $src = trim($call['src'] ?? '');
        $dst = trim($call['dst'] ?? '');
        $channel = trim($call['channel'] ?? '');
        $dstchannel = trim($call['dstchannel'] ?? '');

        foreach ($extensionsList as $ext) {
            if (
                $src === $ext || 
                $dst === $ext || 
                str_contains($channel, "SIP/{$ext}-") || 
                str_contains($channel, "PJSIP/{$ext}-") ||
                str_contains($dstchannel, "SIP/{$ext}-") || 
                str_contains($dstchannel, "PJSIP/{$ext}-")
            ) {
                return true;
            }
        }
        return false;
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
            $user = Auth::user();
            $userRole = $user->role ?? 'agent';
            $company = $user->company ?? null;
            
            // Validamos si es superadmin de forma limpia
            $isSuperAdmin = in_array($userRole, ['superadmin', 'super_admin']);
    
            // 1. Minutos Contratados / Recargados Históricos Totales
            $minutosPaqueteTotal = 0;
            if ($isSuperAdmin) {
                // SUPERADMIN: Suma global de TODAS las compañías del sistema
                $minutosPaqueteTotal = DB::table('company_minute_logs')->sum('minutes_added');
                if ($minutosPaqueteTotal == 0) {
                    $minutosPaqueteTotal = DB::table('companies')->sum('minutes_limit');
                }
            } elseif ($company) {
                // ADMIN / OTROS: Solo su empresa
                $minutosPaqueteTotal = DB::table('company_minute_logs')
                    ->where('company_id', $company->id)
                    ->sum('minutes_added');
                
                if ($minutosPaqueteTotal == 0 && isset($company->minutes_limit)) {
                    $minutosPaqueteTotal = $company->minutes_limit;
                }
            }
    
            // 2. Extensiones permitidas según rol (Si es superadmin, toma TODAS las extensiones del sistema)
            $extensionsList = $this->getAllowedExtensions($user);
    
            // 3. Rangos de fechas dinámicos (Basados puramente en la creación de la compañía)
            $fechaInicioHistorica = null;
    
            if ($isSuperAdmin) {
                $earliest = DB::table('companies')->min('created_at');
                $fechaInicioHistorica = $earliest ? Carbon::parse($earliest)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
            } elseif ($company && !empty($company->created_at)) {
                $fechaInicioHistorica = Carbon::parse($company->created_at)->format('Y-m-d');
            } else {
                $fechaInicioHistorica = Carbon::now('America/Bogota')->startOfMonth()->format('Y-m-d');
            }
    
            $todayStr = Carbon::today('America/Bogota')->format('Y-m-d');
            
            $fechaInicioFiltro = $request->input('fecha_inicio', $todayStr);
            $fechaFinFiltro = $request->input('fecha_fin', $todayStr);
    
            $callsToday = 0;
            $answeredCount = 0;
            $totalMinutosConsumidos = 0; // Conteo directo: 1 llamada válida = 1 minuto
            $successRate = '0%';
            $avgDuration = '00:00';
            $recentCalls = [];
    
            if (!empty($extensionsList) || $isSuperAdmin) {
                
                // A. CONSULTA HISTÓRICA COMPLETA (Para el cálculo exacto de la bolsa de minutos por conteo de llamadas)
                $rawCallsHistoricas = $this->fetchCallsFromApi($fechaInicioHistorica, $todayStr, $isSuperAdmin ? [] : $extensionsList);
                
                foreach ($rawCallsHistoricas as $call) {
                    $src = trim($call['src'] ?? '');
                    $dst = trim($call['dst'] ?? '');
                    $disposition = strtoupper(trim($call['disposition'] ?? ''));
        
                    // REGLA 1: Solo llamadas contestadas (ANSWERED)
                    if ($disposition !== 'ANSWERED') {
                        continue;
                    }

                    // NUEVA REGLA: Descartar códigos de servicio de Asterisk (* o #)
                    if (str_starts_with($dst, '*') || str_starts_with($dst, '#')) {
                        continue;
                    }
        
                    // REGLA 2: Descartar llamadas internas puras
                    $isInternal = (strlen($src) <= 4 && strlen($dst) <= 4);
                    if ($isInternal) {
                        continue; 
                    }
        
                    // REGLA 3: Solo llamadas salientes consumen de la bolsa
                    $isOutbound = $this->isOutboundCall($call, $extensionsList);
                    
                    if ($isOutbound || $isSuperAdmin) {
                        // 1 llamada contestada = 1 minuto exacto
                        $totalMinutosConsumidos++;
                    }
                }
    
                // B. CONSULTA PARA EL FILTRO ACTUAL (Visualización en tabla y métricas del día/rango)
                $rawCallsFiltro = $this->fetchCallsFromApi($fechaInicioFiltro, $fechaFinFiltro, $isSuperAdmin ? [] : $extensionsList);
    
                foreach ($rawCallsFiltro as $call) {
                    $src = trim($call['src'] ?? '');
                    $dst = trim($call['dst'] ?? '');
                    $disposition = strtoupper(trim($call['disposition'] ?? ''));
                    $uniqueKey = ($call['uniqueid'] ?? '') . '_' . ($call['calldate'] ?? '');
        
                    if (!$isSuperAdmin) {
                        if (!$this->callBelongsToExtensions($call, $extensionsList)) {
                            continue;
                        }
                    }

                    // NUEVA REGLA: Descartar códigos de servicio de Asterisk (* o #) en la visualización
                    if (str_starts_with($dst, '*') || str_starts_with($dst, '#')) {
                        continue;
                    }
        
                    if (!isset($recentCalls[$uniqueKey])) {
                        $isInternal = (strlen($src) <= 4 && strlen($dst) <= 4);
                        $isAnswered = ($disposition === 'ANSWERED');
                        $isOutbound = $this->isOutboundCall($call, $extensionsList);
        
                        if ($isInternal) {
                            $tipoTráfico = 'internal';
                        } else {
                            $tipoTráfico = $isOutbound ? 'outbound' : 'inbound';
                        }
        
                        if ($isAnswered) {
                            $answeredCount++;
                        }
        
                        $durationSecs = intval($call['duration'] ?? $call['billsec'] ?? 0);
                        $displayNumber = $isOutbound ? ($dst ?? '') : ($src ?? '');
        
                        $recentCalls[$uniqueKey] = (object)[
                            'type'       => $tipoTráfico,
                            'number'     => $displayNumber,
                            'duration'   => $durationSecs,
                            'status'     => $isAnswered ? 'Contestada' : 'No Contestada',
                            'disposition'=> $disposition,
                            'created_at' => isset($call['calldate']) ? Carbon::parse($call['calldate'])->setTimezone('America/Bogota')->format('Y-m-d H:i:s') : '',
                        ];
                    }
                }
    
                $recentCalls = array_values($recentCalls);
                
                usort($recentCalls, function ($a, $b) {
                    return strtotime($b->created_at) - strtotime($a->created_at);
                });
    
                $callsToday = count($recentCalls);
    
                if ($callsToday > 0) {
                    $successRate = round(($answeredCount / $callsToday) * 100) . '%';
                }
    
                $answeredFilteredSeconds = 0;
                $answeredFilteredCount = 0;
                foreach ($recentCalls as $rc) {
                    if ($rc->status === 'Contestada') {
                        $answeredFilteredSeconds += $rc->duration;
                        $answeredFilteredCount++;
                    }
                }
    
                if ($answeredFilteredCount > 0) {
                    $avgSeconds = round($answeredFilteredSeconds / $answeredFilteredCount);
                    $avgDuration = gmdate("i:s", (int)$avgSeconds);
                }
            }
    
            // 4. Total consumido mapeado directamente al número de llamadas válidas
            $minutosEmpresaTotal = $totalMinutosConsumidos;
    
            // 5. Estadísticas de consumo mensual por extensión (Mes Actual)
            $currentMonthStart = Carbon::now('America/Bogota')->startOfMonth()->format('Y-m-d');
            $rawCallsMonth = $this->fetchCallsFromApi($currentMonthStart, $todayStr, $isSuperAdmin ? [] : $extensionsList);
            
            $extensionUsageStats = [];
            foreach ($extensionsList as $ext) {
                $extensionUsageStats[$ext] = 0;
            }
    
            foreach ($rawCallsMonth as $call) {
                $disposition = strtoupper(trim($call['disposition'] ?? ''));
                if ($disposition !== 'ANSWERED') {
                    continue;
                }

                $src = trim($call['src'] ?? '');
                $dst = trim($call['dst'] ?? '');

                // NUEVA REGLA: Descartar códigos de servicio de Asterisk (* o #) en estadísticas mensuales
                if (str_starts_with($dst, '*') || str_starts_with($dst, '#')) {
                    continue;
                }

                $isInternal = (strlen($src) <= 4 && strlen($dst) <= 4);
                if ($isInternal) {
                    continue;
                }
    
                foreach ($extensionsList as $ext) {
                    if ($this->isOutboundCall($call, [$ext])) {
                        if (!isset($extensionUsageStats[$ext])) {
                            $extensionUsageStats[$ext] = 0;
                        }
                        $extensionUsageStats[$ext]++;
                        break;
                    }
                }
            }
    
            return view('agent.dashboard', compact(
                'minutosPaqueteTotal',
                'minutosEmpresaTotal',
                'callsToday',
                'successRate',
                'avgDuration',
                'recentCalls',
                'company',
                'userRole',
                'extensionsList',
                'extensionUsageStats'
            ));
        }
}