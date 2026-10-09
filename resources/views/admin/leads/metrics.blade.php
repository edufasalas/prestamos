@extends('layouts.app')

@section('content')
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Métricas y Analítica de Leads'])

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    
    <!-- ENCABEZADO DE LA VISTA -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Panel de Estadísticas</h3>
            <p class="text-xs text-slate-400 mt-0.5">Resumen analítico y comportamientos de las solicitudes del simulador.</p>
        </div>
        <div>
            <a href="{{ route('admin.leads.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-table-list"></i> Ver Tabla de Leads
            </a>
        </div>
    </div>

    <!-- TARJETAS DE KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Leads -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Leads</p>
                <h4 class="text-xl font-extrabold text-slate-800 mt-1">{{ $totalLeads }}</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- Monto Total Solicitado -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Monto Total Cartera</p>
                <h4 class="text-xl font-extrabold text-emerald-600 mt-1">${{ number_format($montoTotal, 2) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <!-- Monto Promedio -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Promedio Solicitado</p>
                <h4 class="text-xl font-extrabold text-blue-600 mt-1">${{ number_format($montoPromedio, 2) }}</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <!-- Leads de Hoy -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Registros de Hoy</p>
                <h4 class="text-xl font-extrabold text-amber-600 mt-1">{{ $leadsHoy }}</h4>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>

    </div>

    <!-- SECCIÓN DE GRÁFICOS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Gráfico por Tipo de Préstamo -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Distribución por Tipo de Préstamo</h4>
            <div class="relative h-64 flex justify-center">
                <canvas id="tipoPrestamoChart"></canvas>
            </div>
        </div>

        <!-- Gráfico por Destino del Crédito -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Distribución por Destino</h4>
            <div class="relative h-64 flex justify-center">
                <canvas id="destinoChart"></canvas>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datos para Tipo de Préstamo
        const tipoCtx = document.getElementById('tipoPrestamoChart').getContext('2d');
        new Chart(tipoCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($porTipo->keys()) !!},
                datasets: [{
                    data: {!! json_encode($porTipo->values()) !!},
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 } } }
                }
            }
        });

        // Datos para Destino del Crédito
        const destinoCtx = document.getElementById('destinoChart').getContext('2d');
        new Chart(destinoCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($porDestino->keys()) !!},
                datasets: [{
                    label: 'Solicitudes',
                    data: {!! json_encode($porDestino->values()) !!},
                    backgroundColor: '#4f46e5',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    });
</script>
@endpush

@endsection