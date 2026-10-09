@extends('layouts.app')

@section('content')
<!-- Topbar Global fuera del container con padding para que ocupe todo el ancho -->
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Historial CDR'])

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">

    <!-- Header de la Vista -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Extensión asignada: <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">{{ $sipExtension ?? 'Sin asignar' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-emerald-200/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Registro de Actividad
            </span>
        </div>
    </div>

    <!-- Filtro de Fechas y Opciones Corporativo -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('agent.cdr.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Fecha Inicial</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}" class="w-full bg-slate-50/80 border border-slate-200 focus:border-slate-400 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none transition-all">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Fecha Final</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}" class="w-full bg-slate-50/80 border border-slate-200 focus:border-slate-400 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none transition-all">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Extensión</label>
                <select name="extension" class="w-full bg-slate-50/80 border border-slate-200 focus:border-slate-400 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none transition-all">
                    <option value="">Todas</option>
                    @foreach($extensionsList ?? [] as $ext)
                        <option value="{{ $ext }}" {{ request('extension') == $ext ? 'selected' : '' }}>{{ $ext }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tipo de Llamada</label>
                <select name="tipo" class="w-full bg-slate-50/80 border border-slate-200 focus:border-slate-400 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none transition-all">
                    <option value="">Todas</option>
                    <option value="saliente" {{ (request('tipo') == 'saliente') ? 'selected' : '' }}>Salientes (Origen mío)</option>
                    <option value="entrante" {{ (request('tipo') == 'entrante') ? 'selected' : '' }}>Entrantes (Destino mío)</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Estado</label>
                <select name="estado" class="w-full bg-slate-50/80 border border-slate-200 focus:border-slate-400 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-700 focus:outline-none transition-all">
                    <option value="">Todos</option>
                    <option value="ANSWERED" {{ (request('estado') == 'ANSWERED') ? 'selected' : '' }}>Contestada (ANSWERED)</option>
                    <option value="NO ANSWER" {{ (request('estado') == 'NO ANSWER') ? 'selected' : '' }}>No Contestada (NO ANSWER)</option>
                    <option value="BUSY" {{ (request('estado') == 'BUSY') ? 'selected' : '' }}>Ocupado (BUSY)</option>
                    <option value="FAILED" {{ (request('estado') == 'FAILED') ? 'selected' : '' }}>Fallida (FAILED)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider py-2.5 px-4 rounded-xl transition-all shadow-md active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filtrar</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla y Vista Móvil Adaptativa -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden p-4 sm:p-6">
        <table id="tabla-cdr" class="w-full text-left border-collapse text-xs whitespace-nowrap">
            <thead>
                <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200 text-[10px]">
                    <th class="py-3.5 px-4">Fecha / Hora</th>
                    <th class="py-3.5 px-4">Cliente / Contacto</th>
                    <th class="py-3.5 px-4">Origen (Src)</th>
                    <th class="py-3.5 px-4">Destino (Dst)</th>
                    <th class="py-3.5 px-4">Estado</th>
                    <th class="py-3.5 px-4">Duración</th>
                    <th class="py-3.5 px-4 text-center no-export">Grabación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                @foreach($llamadas as $row)
                    @php
                        $isAnswered = ($row['disposition'] ?? '') === 'ANSWERED';
                        
                        // Obtenemos de forma segura el teléfono limpio para evitar fallos
                        $telFicha = trim($row['cliente_telefono'] ?? ($row['dst'] ?? ''));
                        // Validamos si de verdad tiene un número de teléfono con longitud útil
                        $hasValidPhone = !empty($telFicha) && strlen($telFicha) > 2;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                            {{ $row['calldate'] ?? 'N/A' }}
                        </td>

                        <!-- Columna del Nombre del Cliente con Acceso a Ficha Seguro -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                @if($hasValidPhone)
                                    <a href="{{ route('agent.cliente.ficha', ['telefono' => $telFicha]) }}" class="font-bold text-indigo-600 hover:underline" title="Ver Ficha y Notas del Cliente">
                                        {{ $row['cliente_nombre'] ?? 'Cliente Nuevo / Sin Registrar' }}
                                    </a>
                                    <a href="{{ route('agent.cliente.ficha', ['telefono' => $telFicha]) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded-lg text-[10px] transition-all border border-slate-300 font-bold" title="Abrir Ficha CRM">
                                        <i class="fa-solid fa-address-book"></i> Ficha
                                    </a>
                                @else
                                    <span class="font-semibold text-slate-700">
                                        {{ $row['cliente_nombre'] ?? 'Llamada de Sistema / Interna' }}
                                    </span>
                                    <span class="bg-slate-100 text-slate-400 px-2 py-1 rounded-lg text-[10px] border border-slate-200 italic">
                                        Sin número
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $row['src'] ?? 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $row['dst'] ?? 'N/A' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] uppercase font-extrabold border {{ $isAnswered ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-rose-50 text-rose-700 border-rose-200/60' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isAnswered ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $row['disposition'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                            {{ $row['duration'] ?? 0 }}s
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(!empty($row['ruta_web']))
                                <div class="flex items-center justify-center">
                                    <audio controls preload="none" class="h-7 w-40 sm:w-48 custom-audio-player">
                                        <source src="{{ $row['ruta_web'] }}" type="audio/wav">
                                        No soportado
                                    </audio>
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400 font-normal italic">Sin audio</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('scripts')
<style>
    /* Estilos corporativos para los controles de DataTables */
    .dt-buttons .dt-button {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        color: #334155 !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        padding: 0.5rem 0.85rem !important;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
        transition: all 0.2s !important;
    }
    .dt-buttons .dt-button:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .dataTables_filter input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        outline: none;
    }
    .dataTables_filter input:focus {
        border-color: #94a3b8;
    }
</style>

<script>
    $(document).ready(function() {
        $('#tabla-cdr').DataTable({
            responsive: true,
            dom: '<"flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4"Bf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-4 text-xs font-semibold text-slate-500"lip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel text-emerald-600 mr-1.5"></i>Excel',
                    title: 'Historial_Llamadas_CDR',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa-solid fa-file-pdf text-rose-600 mr-1.5"></i>PDF',
                    title: 'Historial de Llamadas CDR',
                    orientation: 'portrait',
                    pageSize: 'A4',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'copyHtml5',
                    text: '<i class="fa-solid fa-copy text-slate-600 mr-1.5"></i>Copiar',
                    exportOptions: { columns: ':not(.no-export)' }
                },
                {
                    extend: 'print',
                    text: '<i class="fa-solid fa-print text-slate-600 mr-1.5"></i>Imprimir',
                    exportOptions: { columns: ':not(.no-export)' }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
        });
    });
</script>
@endpush