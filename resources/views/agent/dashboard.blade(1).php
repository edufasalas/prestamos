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
            @endphp

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