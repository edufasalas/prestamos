<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Enterprise - CallerVoip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- Headers de seguridad y permisos para WebRTC y Micrófono en móviles -->
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta http-equiv="Permissions-Policy" content="microphone=(self)">
    <meta http-equiv="Cross-Origin-Opener-Policy" content="same-origin-allow-popups">    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Incluimos Chart.js para las estadísticas gráficas profesionales -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        /* REGLAS CSS PARA COLAPSO EN ESCRITORIO (PC) */
        @media (min-width: 1024px) {
            body.sidebar-collapsed #app-sidebar {
                width: 5rem !important; /* Reducir ancho del menú */
            }
            body.sidebar-collapsed #app-sidebar .sidebar-text {
                display: none !important; /* Ocultar textos dentro del menú */
            }
            body.sidebar-collapsed #main-content {
                margin-left: 5rem !important; /* Mover el contenido principal */
            }
        }
    </style>
</head>
<body class="bg-[#f3f4f6] text-slate-800 font-sans h-screen w-screen flex overflow-hidden m-0 p-0">

    @include('layouts.sidebar')

    <!-- Overlay transparente para móviles -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/50 z-40 hidden lg:hidden transition-opacity backdrop-blur-xs"></div>

    <main id="main-content" class="flex-1 lg:ml-64 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full transition-all duration-300">
        
        <!-- Topbar reutilizable con Softphone activo -->
        @include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Centro de Operaciones'])

        <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
            
            @php
                // Verificamos si el usuario autenticado tiene extensión registrada en user_extensions
                $extensionRecord = auth()->user()->activeExtension ?? auth()->user()->extensions()->first();
                $hasExtension = !empty($extensionRecord && $extensionRecord->extension_number);
                $extenNumber = $hasExtension ? $extensionRecord->extension_number : null;
                $userRole = auth()->user()->role ?? 'agent';
            @endphp

            @php
                $company = auth()->user()->company ?? null;
                $brandColor = $company->primary_color ?? '#2563eb';
            @endphp

            <!-- SECCIÓN FINANCIERA & BILLING REDISEÑADA (Estilo Claro y Ejecutivo) -->
            @if(in_array($userRole, ['superadmin', 'super_admin', 'admin', 'supervisor']))
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6 relative overflow-hidden">
                
                <!-- Cabecera de Billing -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-inner">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900">Bolsa de Minutos & Facturación</h3>
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Control de saldo y recargas corporativas para {{ $company->name ?? 'Corporativa' }}.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700">
                            Rol: {{ ucfirst($userRole) }}
                        </span>
                    </div>
                </div>

                <!-- 3 Tarjetas de Resumen Financiero (Estilo Claro / Billing) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Contratados -->
                    <div class="bg-slate-50/70 border border-slate-200/70 p-5 rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Minutos Contratados</p>
                            <h4 class="text-2xl font-black text-slate-900 mt-1">{{ number_format($minutosPaqueteTotal ?? 0) }} <span class="text-xs font-normal text-slate-500">min</span></h4>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                    </div>

                    <!-- Consumidos -->
                    <div class="bg-slate-50/70 border border-slate-200/70 p-5 rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Minutos Consumidos</p>
                            <h4 class="text-2xl font-black text-amber-600 mt-1">{{ number_format($minutosEmpresaTotal ?? 0) }} <span class="text-xs font-normal text-slate-500">min</span></h4>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>

                    <!-- Restantes -->
                    <div class="bg-slate-50/70 border border-slate-200/70 p-5 rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Minutos Disponibles</p>
                            <h4 class="text-2xl font-black text-emerald-600 mt-1">{{ number_format(max(0, ($minutosPaqueteTotal ?? 0) - ($minutosEmpresaTotal ?? 0))) }} <span class="text-xs font-normal text-slate-500">min</span></h4>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                </div>

                @php
                    $porcentajeConsumo = ($minutosPaqueteTotal ?? 0) > 0 ? min(100, round((($minutosEmpresaTotal ?? 0) / $minutosPaqueteTotal) * 100)) : 0;
                @endphp

                <!-- Barra de Progreso Dinámica Limpia -->
                <div class="space-y-2 pt-1">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-slate-600">Utilización de la Bolsa General</span>
                        <span class="text-slate-900 font-bold">{{ $porcentajeConsumo }}% consumido</span>
                    </div>
                    <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-600 via-amber-500 to-rose-500 transition-all duration-500" style="width: {{ $porcentajeConsumo }}%;"></div>
                    </div>
                </div>

                <!-- SECCIÓN DE HISTORIAL DE RECARGAS / BILLING (Máx 4 visibles + Scroll vertical) -->
                <div class="border-t border-slate-100 pt-5 mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Historial de Recargas y Ajustes</h4>
                        <span class="text-[10px] text-slate-400 font-medium">Registros de {{ $company->name ?? 'esta compañía' }}</span>
                    </div>
                
                    @php
                        // Obtenemos los registros directamente de la compañía actual
                        $rechargesCompany = $recharges ?? \Illuminate\Support\Facades\DB::table('company_minute_logs')
                            ->where('company_id', $company->id ?? null)
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp
                
                    <!-- Contenedor con altura exacta para 4 filas (~200px) y scroll vertical -->
                    <div class="max-h-[200px] overflow-y-auto rounded-2xl border border-slate-200/80 pr-1 scrollbar-thin scrollbar-thumb-slate-200">
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider z-10">
                                <tr>
                                    <th class="py-3 px-4">Tipo</th>
                                    <th class="py-3 px-4">Minutos</th>
                                    <th class="py-3 px-4">Notas / Detalle</th>
                                    <th class="py-3 px-4 text-right">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse($rechargesCompany as $recharge)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-4">
                                            @if($recharge->type == 'recharge')
                                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-[10px] border border-emerald-200">Recarga</span>
                                            @elseif($recharge->type == 'adjustment')
                                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-bold rounded-lg text-[10px] border border-blue-200">Ajuste</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-bold rounded-lg text-[10px] border border-amber-200">Reset</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-extrabold text-slate-900">
                                            {{ $recharge->minutes_added > 0 ? '+' : '' }}{{ number_format($recharge->minutes_added) }} min
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 font-medium">
                                            {{ $recharge->notes ?? 'Sin descripción' }}
                                        </td>
                                        <td class="py-3 px-4 text-right text-slate-400 font-medium">
                                            {{ isset($recharge->created_at) ? \Carbon\Carbon::parse($recharge->created_at)->format('d/m/Y H:i') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-400 text-xs font-medium">
                                            No hay registros de recargas o ajustes financieros para esta compañía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            @endif
            
            <!-- SECCIÓN PROFESIONAL: DESGLOSE DE CONSUMO MENSUAL POR EXTENSIÓN -->
            @if(in_array($userRole, ['superadmin', 'super_admin', 'admin', 'supervisor']))
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden mt-6">
                <div class="p-6 sm:px-8 sm:pt-7 sm:pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Desglose de Consumo Mensual por Extensión</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Minutos gastados de cada extensión durante el mes actual.</p>
                    </div>
                    <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-3 py-1 rounded-full border border-blue-200/60 w-fit">
                        Mes en Curso
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Extensión</th>
                                <th class="py-3.5 px-6">Participación del Mes</th>
                                <th class="py-3.5 px-6 text-right">Minutos / Llamadas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @php
                                $totalMesExt = array_sum($extensionUsageStats ?? []) ?: 1;
                            @endphp
                            
                            @forelse($extensionUsageStats ?? [] as $extNum => $minsSpent)
                                @php
                                    $porcentajeExt = min(100, round(($minsSpent / $totalMesExt) * 100));
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-slate-800 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-inner">
                                            <i class="fa-solid fa-phone"></i>
                                        </div>
                                        Extensión {{ $extNum }}
                                    </td>
                                    <td class="py-4 px-6 w-1/3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-slate-100 h-2 rounded-full overflow-hidden">
                                                <div class="bg-blue-600 h-full rounded-full" style="width: {{ $porcentajeExt }}%;"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-500 w-9 text-right">{{ $porcentajeExt }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <span class="font-extrabold text-slate-900 text-base">{{ number_format($minsSpent) }}</span>
                                        <span class="text-xs text-slate-400 font-medium ml-1">min</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400 text-xs font-medium">
                                        No hay extensiones registradas o con consumo activo para este periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif            

            <!-- Tarjetas de Métricas Superiores -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Llamadas Hoy</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $callsToday ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tiempo Promedio</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $avgDuration ?? '00:00' }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Llamadas Exitosas</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $successRate ?? '0%' }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <!-- 4to Card adaptado a la tabla user_extensions -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estado Extensión</p>
                        @if($hasExtension)
                            <h3 id="sip-badge-text" class="text-base font-extrabold text-emerald-600 tracking-tight mt-1">En Línea</h3>
                            <span id="sip-badge-sub" class="inline-block text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">Ext: {{ $extenNumber }}</span>
                        @else
                            <h3 id="sip-badge-text" class="text-base font-extrabold text-rose-600 tracking-tight mt-1">Sin Registro</h3>
                            <span id="sip-badge-sub" class="inline-block text-[10px] text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">Configurar BD</span>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-2xl {{ $hasExtension ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DE GRÁFICOS Y ESTADÍSTICAS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Gráfico de Línea: Comportamiento de llamadas en el día -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs lg:col-span-2 flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Flujo de Llamadas por Hora</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Distribución horaria de actividad de la extensión</p>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-200/60">Tiempo Real</span>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="callsFlowChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Dona: Distribución Entrantes vs Salientes -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="mb-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Tipo de Tráfico</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Entrantes vs Salientes</p>
                    </div>
                    <div class="relative h-52 w-full flex items-center justify-center">
                        <canvas id="callsDistributionChart"></canvas>
                    </div>
                    <div class="mt-4 flex justify-around text-center border-t border-slate-100 pt-3">
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase">Entrantes</span>
                            <span id="lblInboundPct" class="text-sm font-extrabold text-blue-600">0%</span>
                        </div>
                        <div class="border-r border-slate-100"></div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase">Salientes</span>
                            <span id="lblOutboundPct" class="text-sm font-extrabold text-emerald-600">0%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    @include('agent.components.softphone')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (window.innerWidth >= 1024) {
                document.body.classList.toggle('sidebar-collapsed');
            } else {
                if (sidebar) sidebar.classList.toggle('-translate-x-full');
                if (overlay) overlay.classList.toggle('hidden');
            }
        }

        function toggleSoftphone() {
            const softphoneDrawer = document.getElementById('softphone-drawer');
            if (softphoneDrawer) softphoneDrawer.classList.toggle('translate-x-full');
        }

        // --- INICIALIZACIÓN DE GRÁFICOS ESTADÍSTICOS CON DATOS 100% REALES ---
        document.addEventListener("DOMContentLoaded", function() {
            const recentCallsData = @json($recentCalls ?? []);

            const hoursLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'];
            let hoursCounts = [0, 0, 0, 0, 0, 0];
            let inboundCount = 0;
            let outboundCount = 0;

            recentCallsData.forEach(call => {
                if (call.type === 'inbound') {
                    inboundCount++;
                } else {
                    outboundCount++;
                }

                if (call.created_at) {
                    let parts = call.created_at.split(' ');
                    if (parts[1]) {
                        let hourPart = parseInt(parts[1].split(':')[0]);
                        if (hourPart >= 8 && hourPart < 10) hoursCounts[0]++;
                        else if (hourPart >= 10 && hourPart < 12) hoursCounts[1]++;
                        else if (hourPart >= 12 && hourPart < 14) hoursCounts[2]++;
                        else if (hourPart >= 14 && hourPart < 16) hoursCounts[3]++;
                        else if (hourPart >= 16 && hourPart < 18) hoursCounts[4]++;
                        else if (hourPart >= 18) hoursCounts[5]++;
                    }
                }
            });

            const totalTraffic = inboundCount + outboundCount;
            const inboundPct = totalTraffic > 0 ? Math.round((inboundCount / totalTraffic) * 100) : 0;
            const outboundPct = totalTraffic > 0 ? Math.round((outboundCount / totalTraffic) * 100) : 0;

            const lblInbound = document.getElementById('lblInboundPct');
            const lblOutbound = document.getElementById('lblOutboundPct');
            if (lblInbound) lblInbound.textContent = inboundPct + '%';
            if (lblOutbound) lblOutbound.textContent = outboundPct + '%';

            // Gráfico de Flujo de Llamadas (Líneas)
            const ctxFlow = document.getElementById('callsFlowChart').getContext('2d');
            new Chart(ctxFlow, {
                type: 'line',
                data: {
                    labels: hoursLabels,
                    datasets: [{
                        label: 'Llamadas',
                        data: hoursCounts,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, precision: 0 } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

            // Gráfico de Distribución (Dona)
            const ctxDist = document.getElementById('callsDistributionChart').getContext('2d');
            new Chart(ctxDist, {
                type: 'doughnut',
                data: {
                    labels: ['Entrantes', 'Salientes'],
                    datasets: [{
                        data: totalTraffic > 0 ? [inboundPct, outboundPct] : [0, 0],
                        backgroundColor: ['#2563eb', '#059669'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } },
                    cutout: '75%'
                }
            });
        });

        function changeAgentStatus(status) {
            console.log("Estado del agente cambiado a:", status);
        }
    </script>
</body>
</html>