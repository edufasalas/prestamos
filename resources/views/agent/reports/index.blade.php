@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full">
    
    <!-- Topbar Corporativo -->
    @include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Métricas & Reportes'])

    <!-- Contenido Principal con Scroll -->
    <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado de Sección -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Rendimiento & Métricas de Llamadas</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Analítica en tiempo real del tráfico telefónico (Datos reales de Issabel).</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar text-slate-400"></i> Hoy
                    </button>
                    <button onclick="window.print()" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-file-arrow-down"></i> Exportar
                    </button>
                </div>
            </div>

            <!-- KPI Cards Dinámicas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total llamadas -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Total Llamadas</span>
                        <i class="fa-solid fa-phone-volume text-blue-600"></i>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900">{{ $callsToday ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Sincronizado</span>
                    </div>
                </div>

                <!-- Llamadas Contestadas / Tasa de Éxito -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Tasa de Éxito</span>
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900">{{ $successRate ?? '0%' }}</span>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Efectividad</span>
                    </div>
                </div>

                <!-- Tiempo Promedio -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Promedio (Talk Time)</span>
                        <i class="fa-solid fa-stopwatch text-amber-500"></i>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900">{{ $avgDuration ?? '00:00' }}</span>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">Duración</span>
                    </div>
                </div>

                <!-- Estado Conexión SIP -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-xs font-bold uppercase tracking-wider">Canal Extensión</span>
                        <i class="fa-solid fa-server text-teal-600"></i>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-lg font-black text-teal-600 truncate">Ext: {{ $sipExtension ?? 'N/D' }}</span>
                        <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full">Activo</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico Dinámico con Chart.js (100% Real) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Tráfico por Horas del Día</h2>
                    <span id="peak-hour-label" class="text-[11px] font-semibold text-slate-400">Analizando registros...</span>
                </div>

                <div class="relative h-64 w-full">
                    <canvas id="callsAnalyticsChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Script para procesar las llamadas reales sin inventar datos -->
<!-- Script para procesar llamadas reales de la API sin datos falsos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datos reales obtenidos del controlador (filtrados por usuario y extensión)
        const recentCalls = @json($recentCalls ?? []);

        const hoursLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'];
        let hoursCounts = [0, 0, 0, 0, 0, 0];

        // Si hay llamadas reales, las distribuimos en su hora correspondiente
        if (Array.isArray(recentCalls) && recentCalls.length > 0) {
            recentCalls.forEach(call => {
                if (call.created_at) {
                    let parts = call.created_at.split(' ');
                    if (parts[1]) {
                        let hour = parseInt(parts[1].split(':')[0]);
                        if (hour >= 8 && hour < 10) hoursCounts[0]++;
                        else if (hour >= 10 && hour < 12) hoursCounts[1]++;
                        else if (hour >= 12 && hour < 14) hoursCounts[2]++;
                        else if (hour >= 14 && hour < 16) hoursCounts[3]++;
                        else if (hour >= 16 && hour < 18) hoursCounts[4]++;
                        else if (hour >= 18) hoursCounts[5]++;
                    }
                }
            });
        }

        // Renderizado del gráfico en ceros si no hay actividad
        const ctx = document.getElementById('callsAnalyticsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: hoursLabels,
                datasets: [{
                    label: 'Llamadas Registradas',
                    data: hoursCounts, // Serán [0,0,0,0,0,0] si no hay llamadas
                    backgroundColor: '#0f172a',
                    hoverBackgroundColor: '#059669',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, stepSize: 1 } 
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 10 } } 
                    }
                }
            }
        });

        // Mensaje de estado real en la interfaz
        const peakLabel = document.getElementById('peak-hour-label');
        if (peakLabel) {
            let maxVal = Math.max(...hoursCounts);
            if (maxVal > 0) {
                let maxIndex = hoursCounts.indexOf(maxVal);
                peakLabel.textContent = `Pico máximo: ${hoursLabels[maxIndex]} (${maxVal} llamadas)`;
            } else {
                peakLabel.textContent = "Sin tráfico de llamadas registrado para esta extensión hoy";
            }
        }
    });
</script>
@endsection