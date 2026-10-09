@extends('layouts.app')

@section('content')
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Directorio de Clientes'])

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    
    <!-- Botón Abrir Modal Nuevo Cliente -->
    <button onclick="document.getElementById('modal-nuevo-cliente').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer" title="Registrar nuevo cliente">
        <i class="fa-solid fa-user-plus"></i> Nuevo cliente
    </button>
    
    <!-- Cabecera y Buscador en Tiempo Real -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Cartera de Clientes</h3>
            <p class="text-xs text-slate-400 mt-0.5">Ordenado por actividad reciente. Búsqueda instantánea en vivo.</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap sm:flex-nowrap justify-end">
            <!-- Input de Búsqueda AJAX -->
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="search-input" value="{{ $search ?? '' }}" placeholder="Escribe para buscar..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:border-slate-400">
            </div>

            <!-- Botones de Acción Rápida -->
            <div class="flex items-center gap-2">

                <!-- Botón Exportar -->
                <a href="{{ route('agent.cliente.export') }}" class="bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs" title="Descargar base">
                    <i class="fa-solid fa-file-arrow-down text-slate-700"></i> Exportar
                </a>
            
                <!-- Botón Abrir Modal Importar -->
                <button onclick="document.getElementById('modal-importar').classList.remove('hidden')" class="bg-slate-900 hover:bg-black text-white px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs cursor-pointer" title="Subir archivo">
                    <i class="fa-solid fa-file-arrow-up"></i> Importar
                </button>
            
                <!-- Botón Ayuda (Help) -->
                <button onclick="document.getElementById('modal-ayuda').classList.remove('hidden')" class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 w-9 h-9 rounded-xl text-xs font-bold transition-all flex items-center justify-center shadow-xs cursor-pointer" title="Instrucciones">
                    <i class="fa-solid fa-circle-question text-sm"></i>
                </button>
            </div>
            
            <!-- MODAL DE NUEVO CLIENTE -->
            <div id="modal-nuevo-cliente" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-slate-300 shadow-xl max-w-md w-full p-6 space-y-4 text-left">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-user-plus text-slate-900"></i> Registrar Nuevo Cliente
                        </h3>
                        <button onclick="document.getElementById('modal-nuevo-cliente').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold text-sm cursor-pointer">✕</button>
                    </div>
                    
                    <form action="{{ route('agent.cliente.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Completo *</label>
                            <input type="text" name="nombre" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-400" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Teléfono *</label>
                            <input type="text" name="telefono" placeholder="Ej: 3001234567" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-400" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Correo Electrónico</label>
                            <input type="email" name="correo" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-400">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Empresa</label>
                                <input type="text" name="empresa" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-400">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Estado CRM</label>
                                <select name="estado_crm" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-400">
                                    <option value="Prospecto">Prospecto</option>
                                    <option value="Interesado">Interesado</option>
                                    <option value="Cliente Activo">Cliente Activo</option>
                                    <option value="Seguimiento">Seguimiento</option>
                                    <option value="No Interesado">No Interesado</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Dirección</label>
                            <input type="text" name="direccion" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-400">
                        </div>
                        
                        <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                            <button type="button" onclick="document.getElementById('modal-nuevo-cliente').classList.add('hidden')" class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold text-xs px-4 py-2 rounded-xl transition-all cursor-pointer">Cancelar</button>
                            <button type="submit" class="bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider px-5 py-2 rounded-xl transition-all shadow-sm cursor-pointer">Guardar Cliente</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL DE IMPORTACIÓN -->
            <div id="modal-importar" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-slate-300 shadow-xl max-w-md w-full p-6 space-y-5 text-left">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Importar Clientes Masivamente</h3>
                        <button onclick="document.getElementById('modal-importar').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold text-sm cursor-pointer">✕</button>
                    </div>
                    
                    <form action="{{ route('agent.cliente.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">Selecciona archivo CSV o Excel (.csv)</label>
                            <input type="file" name="archivo_excel" accept=".csv, .txt, .xlsx" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-2.5 text-xs font-medium text-slate-900 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-black cursor-pointer" required>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            El archivo debe contener las columnas en orden: <strong class="text-slate-900">Nombre, Teléfono, Correo, Empresa, Dirección, Estado CRM</strong>.
                        </p>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="document.getElementById('modal-importar').classList.add('hidden')" class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold text-xs px-4 py-2 rounded-xl transition-all cursor-pointer">Cancelar</button>
                            <button type="submit" class="bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider px-5 py-2 rounded-xl transition-all shadow-sm cursor-pointer">Subir y Procesar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- MODAL DE AYUDA (HELP) -->
            <div id="modal-ayuda" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-slate-300 shadow-xl max-w-lg w-full p-6 space-y-5 text-left">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-slate-900"></i> Guía para Importar Clientes
                        </h3>
                        <button onclick="document.getElementById('modal-ayuda').classList.add('hidden')" class="text-slate-500 hover:text-slate-900 font-bold text-sm cursor-pointer">✕</button>
                    </div>
            
                    <div class="space-y-3 text-xs text-slate-700 leading-relaxed">
                        <p>Para asegurar que la base de datos cargue la información correctamente, sigue estas pautas:</p>
                        <div class="bg-slate-50 border border-slate-300 p-3.5 rounded-xl space-y-2">
                            <strong class="text-slate-900 uppercase text-[10px] tracking-wider block">Estructura del archivo CSV:</strong>
                            <p class="font-mono text-[11px] text-slate-900 bg-white p-2 border border-slate-200 rounded-lg">
                                Nombre, Teléfono, Correo, Empresa, Dirección, Estado CRM
                            </p>
                        </div>
                        <ul class="list-disc pl-4 space-y-1 text-slate-700">
                            <li><strong>Teléfono:</strong> Es el campo clave. Si un número ya existe en el sistema, actualizará los datos.</li>
                            <li><strong>Estados válidos:</strong> Prospecto, Interesado, Cliente Activo, No Interesado, Seguimiento.</li>
                        </ul>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="button" onclick="document.getElementById('modal-ayuda').classList.add('hidden')" class="bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider px-5 py-2 rounded-xl transition-all shadow-sm cursor-pointer">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Corporativa -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Cliente / Empresa</th>
                        <th class="py-3.5 px-4">Teléfono</th>
                        <th class="py-3.5 px-4">Correo</th>
                        <th class="py-3.5 px-4">Estado CRM</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientes-table-body" class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                    @include('agent.cliente.partials.table_body', ['clientes' => $clientes])
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div id="pagination-container" class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $clientes->links() }}
        </div>
    </div>

</div>

<!-- Script de AJAX en Tiempo Real -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const tableBody = document.getElementById('clientes-table-body');
        let timeout = null;

        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            let query = this.value;

            timeout = setTimeout(() => {
                fetch(`{{ route('agent.cliente.index') }}?search=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableBody.innerHTML = html;
                })
                .catch(error => console.error('Error en búsqueda:', error));
            }, 300);
        });
    });
</script>
@endsection