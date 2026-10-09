@extends('layouts.app')

@section('content')
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Directorio de Leads y Simulador'])

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    
    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Cartera de Leads (Simulador)</h3>
            <p class="text-xs text-slate-400 mt-0.5">Listado completo de solicitudes con detalles y opciones de exportación.</p>
        </div>
    </div>

    <!-- Tabla DataTables -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="overflow-x-auto w-full">
            <table id="leadsTable" class="min-w-full divide-y divide-slate-200 text-left w-full">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3">CURP</th>
                        <th class="px-4 py-3">Monto</th>
                        <th class="px-4 py-3">Plazo</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Teléfono</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Ingreso</th>
                        <th class="px-4 py-3">Laboral</th>
                        <th class="px-4 py-3">Destino</th>
                        <th class="px-4 py-3">Contacto</th>
                        <th class="px-4 py-3">Horario</th>
                        <th class="px-4 py-3">IP</th>
                        <th class="px-4 py-3">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs text-slate-700">
                    @foreach($leads as $lead)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">{{ $lead->curp }}</td>
                        <td class="px-4 py-3 font-medium text-emerald-600 whitespace-nowrap">${{ number_format($lead->monto, 2) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->plazo }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $lead->correo }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->telefono }}</td>
                        <td class="px-4 py-3 whitespace-nowrap uppercase">{{ $lead->tipo_prestamo }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->ingreso }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->laboral }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->destino }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->contacto }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $lead->horario }}</td>
                        <td class="px-4 py-3 text-slate-400 whitespace-nowrap">{{ $lead->ip }}</td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $lead->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<!-- DataTables & Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#leadsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            responsive: true,
            scrollX: true,
            dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4 gap-2"<"mb-2 sm:mb-0 w-full sm:w-auto"l><"flex flex-wrap gap-2 w-full sm:w-auto"B><"w-full sm:w-auto"f>>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-2"<"mb-2 sm:mb-0"i>p>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel mr-1"></i> Excel',
                    className: 'bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa-solid fa-file-pdf mr-1"></i> PDF',
                    className: 'bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs'
                },
                {
                    extend: 'print',
                    text: '<i class="fa-solid fa-print mr-1"></i> Imprimir',
                    className: 'bg-slate-600 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs'
                }
            ]
        });
    });
</script>
@endpush

@endsection