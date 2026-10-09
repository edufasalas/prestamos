<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class CdrController extends Controller
{
    /**
     * Consulta la API enviando las extensiones permitidas correspondientes.
     */
    private function fetchCallsFromApi(string $fechaInicio, string $fechaFin, array $extensionsList = [], ?string $companyDid = null): array
    {
        $extensionsString = !empty($extensionsList) ? implode(',', $extensionsList) : 'all';
    
        $response = Http::get("https://servidores.voiparkas.com/cdrweb/llamacrm/api_cdr_acall.php", [
            'extension'    => $extensionsString,
            'did'          => $companyDid, // <-- ¡Nuevo: enviamos el DID de la empresa!
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin
        ]);
    
        if ($response->successful()) {
            $data = $response->json();
            return $data['llamadas'] ?? [];
        }
    
        return [];
    }

    /**
     * Obtiene las extensiones permitidas según el rol jerárquico del usuario en Laravel.
     */
    private function getAllowedExtensions($user): array
    {
        if ($user->role === 'superadmin' || $user->role === 'super_admin') {
            // CORRECCIÓN CLAVE: El superadmin obtiene las extensiones de TODAS las empresas creadas en el CRM (usuarios con company_id no nulo)
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

public function index(Request $request)
    {
        $user = Auth::user();

        $companyDid = null;
        if ($user->company_id) {
            $companyDid = DB::table('companies')->where('id', $user->company_id)->value('incoming_did');
        } 
        
        // 1. Obtenemos las extensiones permitidas base según el rol
        $extensionsList = $this->getAllowedExtensions($user);

        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $estadoFiltro = $request->input('estado');
        $extensionSeleccionada = $request->input('extension');

        $llamadas = [];

        // Si es superadmin o tiene extensiones asignadas, procedemos a consultar la API
        if (!empty($extensionsList) || in_array($user->role, ['super_admin', 'superadmin'])) {
            
            // Si es superadmin y la lista local está vacía, pasamos array vacío para que pida todo lo de las empresas
            $queryExtensions = in_array($user->role, ['super_admin', 'superadmin']) ? $extensionsList : $extensionsList;
            
            $rawCalls = $this->fetchCallsFromApi($fechaInicio, $fechaFin, $queryExtensions, $companyDid);

            $sipExtension = !empty($extensionsList) ? implode(', ', $extensionsList) : 'N/A';
            $processedCalls = [];

        foreach ($rawCalls as $call) {
                $src = $call['src'] ?? '';
                $dst = $call['dst'] ?? '';
                $channel = $call['channel'] ?? '';
                $dstchannel = $call['dstchannel'] ?? '';

                // Función auxiliar interna para saber si la llamada pertenece a una lista de extensiones
                $belongsToExtensions = function($exts) use ($src, $dst, $channel, $dstchannel, $call, $companyDid) {
                    // Si el DID de la llamada coincide con el de la empresa, es válida de inmediato
                    if (!empty($companyDid) && ($call['did'] ?? '') === $companyDid) {
                        return true;
                    }
                
                    // Tu lógica original de extensiones intacta:
                    foreach ($exts as $ext) {
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
                };

                // 1. Filtro de seguridad para roles que no sean superadmin
                if (!in_array($user->role, ['super_admin', 'superadmin'])) {
                    if (!$belongsToExtensions($extensionsList)) {
                        continue; 
                    }
                }

                // 2. Filtro manual por el selector de extensión de la vista si se usó
                if (!empty($extensionSeleccionada)) {
                    if (!$belongsToExtensions([$extensionSeleccionada])) {
                        continue;
                    }
                }

                // Filtro por estado de llamada (ANSWERED, etc.)
                if (!empty($estadoFiltro)) {
                    if (strtoupper($call['disposition'] ?? '') !== strtoupper($estadoFiltro)) {
                        continue;
                    }
                }

                $uniqueKey = ($call['uniqueid'] ?? '') . '_' . ($call['calldate'] ?? '');
                $processedCalls[$uniqueKey] = $call;
            }

            uasort($processedCalls, function ($a, $b) {
                return strtotime($b['calldate'] ?? 0) - strtotime($a['calldate'] ?? 0);
            });

            $llamadas = array_values($processedCalls);

            // --- INTEGRACIÓN CRM: EXTRACCIÓN DIRECTA SEGÚN DATOS DE ISSABEL ---
            if (!empty($llamadas)) {
                $telefonosAConsultar = [];

                foreach ($llamadas as $call) {
                    $src = trim($call['src'] ?? '');
                    $dst = trim($call['dst'] ?? '');

                    // En Issabel, el número del cliente es el que NO es la extensión interna del agente
                    $numeroCliente = '';

                    // Si el src es una extensión interna o corta, el cliente es el dst
                    if (in_array($src, $extensionsList) || strlen($src) <= 6) {
                        $numeroCliente = $dst;
                    } 
                    // Si el dst es una extensión interna o corta, el cliente es el src
                    elseif (in_array($dst, $extensionsList) || strlen($dst) <= 6) {
                        $numeroCliente = $src;
                    } 
                    // Por defecto si ninguno encaja, tomamos el dst si es largo, sino el src
                    else {
                        $numeroCliente = strlen($dst) >= strlen($src) ? $dst : $src;
                    }

                    if (!empty($numeroCliente)) {
                        $telefonosAConsultar[] = $numeroCliente;
                        // Guardamos también su variante limpia de últimos 10 dígitos (para evitar líos con el '57')
                        $telefonosAConsultar[] = substr(preg_replace('/[^0-9]/', '', $numeroCliente), -10);
                    }
                }

                // Consultamos de forma masiva en la tabla local 'clientes'
                $clientesData = DB::table('clientes')
                                        ->whereIn('telefono', array_unique($telefonosAConsultar))
                                        ->get(['nombre', 'telefono']);

                $clientesMap = [];
                foreach ($clientesData as $c) {
                    $clientesMap[$c->telefono] = $c->nombre;
                    $ultimos10 = substr(preg_replace('/[^0-9]/', '', $c->telefono), -10);
                    $clientesMap[$ultimos10] = $c->nombre;
                }

                // Inyectamos el nombre correspondiente a cada llamada
                foreach ($llamadas as &$call) {
                    $src = trim($call['src'] ?? '');
                    $dst = trim($call['dst'] ?? '');

                    $numeroCliente = '';
                    if (in_array($src, $extensionsList) || strlen($src) <= 6) {
                        $numeroCliente = $dst;
                    } elseif (in_array($dst, $extensionsList) || strlen($dst) <= 6) {
                        $numeroCliente = $src;
                    } else {
                        $numeroCliente = strlen($dst) >= strlen($src) ? $dst : $src;
                    }

                    // BLINDAJE OBLIGATORIO: Aseguramos que la clave exista siempre para evitar el error 500 del administrador
                    $call['cliente_telefono'] = !empty($numeroCliente) ? trim($numeroCliente) : null;
                    
                    $numLimpio10 = !empty($numeroCliente) ? substr(preg_replace('/[^0-9]/', '', $numeroCliente), -10) : '';

                    // Buscamos coincidencia exacta o por los últimos 10 dígitos
                    $call['cliente_nombre'] = ($numeroCliente && isset($clientesMap[$numeroCliente])) 
                        ? $clientesMap[$numeroCliente] 
                        : (($numLimpio10 && isset($clientesMap[$numLimpio10])) 
                            ? $clientesMap[$numLimpio10] 
                            : 'Cliente Nuevo / Sin Registrar');
                }
                unset($call);
            }
            // --- FIN DE INTEGRACIÓN CRM ---

        } else {
            $sipExtension = 'N/A';
        }

        return view('agent.cdr.index', compact('llamadas', 'fechaInicio', 'fechaFin', 'sipExtension', 'extensionsList'));
    }   
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        $companyDid = null;
        if ($user->company_id) {
            $companyDid = DB::table('companies')->where('id', $user->company_id)->value('incoming_did');
        }        
        $extensionsList = $this->getAllowedExtensions($user);
        
        $sipExtension = !empty($extensionsList) ? implode(', ', $extensionsList) : '99999';
        
        // Estrictamente la fecha de HOY en Colombia
        $todayStr = Carbon::today('America/Bogota')->format('Y-m-d');

        $callsToday = 0;
        $answeredCount = 0;
        $totalSeconds = 0;
        $successRate = '0%';
        $avgDuration = '00:00';
        $recentCalls = [];
        $userRole = $user->role ?? '';

        if (!empty($extensionsList) || in_array($user->role, ['super_admin', 'superadmin'])) {
            $queryExtensions = $extensionsList;
            
            // Consultar únicamente HOY
            $rawCalls = $this->fetchCallsFromApi($todayStr, $todayStr, $queryExtensions, $companyDid);

            $allRawCalls = [];

            // Validación por canales idéntica a la de tu CdrController
            $belongsToExtensions = function($exts) use ($src, $dst, $channel, $dstchannel, $call, $companyDid) {
                // Si el DID de la llamada coincide con el DID de la empresa, es válida de inmediato
                if (!empty($companyDid) && ($call['did'] ?? '') === $companyDid) {
                    return true;
                }
            
                // Tu lógica original de extensiones y canales intacta:
                foreach ($exts as $ext) {
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
            };

            foreach ($rawCalls as $call) {
                $src = $call['src'] ?? '';
                $dst = $call['dst'] ?? '';
                $channel = $call['channel'] ?? '';
                $dstchannel = $call['dstchannel'] ?? '';

                // Si no es superadmin, validamos por extensión y por canal
                if (!in_array($user->role, ['super_admin', 'superadmin'])) {
                    if (!$belongsToExtensions($extensionsList, $src, $dst, $channel, $dstchannel)) {
                        continue;
                    }
                }

                $uniqueKey = ($call['uniqueid'] ?? '') . '_' . ($call['calldate'] ?? '');
                $allRawCalls[$uniqueKey] = $call;
            }

            uasort($allRawCalls, function ($a, $b) {
                return strtotime($b['calldate'] ?? 0) - strtotime($a['calldate'] ?? 0);
            });

            $rawLlamadas = array_values($allRawCalls);
            $callsToday = count($rawLlamadas);

            foreach ($rawLlamadas as $call) {
                $disposition = strtoupper($call['disposition'] ?? '');
                $isAnswered = ($disposition === 'ANSWERED');
                
                if ($isAnswered) {
                    $answeredCount++;
                }

                $durationSecs = intval(str_replace('s', '', $call['duration'] ?? $call['billsec'] ?? '0'));
                if ($isAnswered) {
                    $totalSeconds += $durationSecs;
                }

                $src = $call['src'] ?? '';
                $isOutbound = in_array($src, $extensionsList) || str_contains($call['channel'] ?? '', "SIP/{$src}-") || str_contains($call['channel'] ?? '', "PJSIP/{$src}-");

                $recentCalls[] = (object)[
                    'type'       => $isOutbound ? 'outbound' : 'inbound',
                    'number'     => $isOutbound ? ($call['dst'] ?? '') : ($src ?? ''),
                    'duration'   => $durationSecs,
                    'status'     => $isAnswered ? 'Contestada' : 'No Contestada',
                    'created_at' => isset($call['calldate']) ? Carbon::parse($call['calldate'])->setTimezone('America/Bogota')->format('Y-m-d H:i:s') : '',
                ];
            }

            if ($callsToday > 0) {
                $successRate = round(($answeredCount / $callsToday) * 100) . '%';
            }

            if ($answeredCount > 0) {
                $avgSeconds = round($totalSeconds / $answeredCount);
                $avgDuration = gmdate("i:s", (int)$avgSeconds);
            }
        }

        return view('agent.reports.index', compact(
            'sipExtension',
            'callsToday',
            'successRate',
            'avgDuration',
            'recentCalls',
            'userRole'
        ));
    }
}