@extends('layouts.app') {{-- O el layout base que utilices que incluya el diseño que pasaste --}}

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-y-auto bg-[#f8fafc] p-6 lg:p-10" x-data="campaignManager()">
    
    <!-- HEADER DE LA SECCIÓN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shadow-inner">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                Gestión de Campañas de Discado (Predictivo / Masivo)
            </h1>
            <p class="text-xs text-slate-500 mt-1">Configura campañas automatizadas por extensión, sube listas de leads por CSV y vincula tus audios.</p>
        </div>
        
        <!-- Botón para abrir Modal de Creación -->
        <button @click="openModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-5 py-3 rounded-2xl shadow-sm transition-all flex items-center gap-2 cursor-pointer w-fit">
            <i class="fa-solid fa-plus"></i> Nueva Campaña
        </button>
    </div>

    <!-- ALERTAS DE ÉXITO O ERROR -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold p-4 rounded-2xl flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold p-4 rounded-2xl flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-triangle-exclamation text-base text-rose-600"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- TABLA DE CAMPAÑAS EXISTENTES -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Campañas Registradas</h3>
            <div class="flex items-center gap-4">
                <span class="text-[10px] text-slate-400 font-medium">
                    Extensión: <strong class="text-slate-700">{{ \App\Models\UserExtension::where('user_id', auth()->id())->value('extension_number') ?? 'No configurada' }}</strong>
                </span>
                <span class="text-[10px] text-slate-400 font-medium">Compañía ID: {{ auth()->user()->company_id ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Campaña</th>
                        <th class="py-3.5 px-6">Tipo</th>
                        <th class="py-3.5 px-6">Destino / Cola</th>
                        <th class="py-3.5 px-6">Extensión Origen</th>
                        <th class="py-3.5 px-6 text-center">Canales Máx.</th>
                        <th class="py-3.5 px-6 text-center">Estado</th>
                        <th class="py-3.5 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($campaigns ?? [] as $camp)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900">{{ $camp->name }}</td>
                            <td class="py-4 px-6">
                                @if($camp->campaign_type == 'queue')
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-lg text-[10px] border border-indigo-200">Cola (Agentes)</span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-bold rounded-lg text-[10px] border border-amber-200">Broadcast (Audio)</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-semibold">{{ $camp->queue_number ?? 'N/A' }}</td>
                            <td class="py-4 px-6 text-slate-600 font-mono text-[11px] font-bold">Ext. {{ $camp->extension ?? auth()->user()->extension ?? '100' }}</td>
                            <td class="py-4 px-6 text-center font-extrabold text-slate-900">{{ $camp->max_channels }}</td>
                            <td class="py-4 px-6 text-center">
                                @if($camp->status == 'running')
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded-full text-[10px] uppercase">Ejecutando</span>
                                @elseif($camp->status == 'paused')
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 font-bold rounded-full text-[10px] uppercase">Detenida</span>
                                @else
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded-full text-[10px] uppercase">{{ $camp->status }}</span>
                                @endif
                            </td>
                            
                            <!-- ACCIONES E INICIO / DETENCIÓN -->
                            <td class="py-4 px-6 text-right flex items-center justify-end gap-2">
                                @if($camp->status != 'running')
                                    <form action="{{ route('campaigns.start', $camp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-play"></i> Iniciar
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('campaigns.stop', $camp->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-xl text-[11px] font-bold shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-stop"></i> Detener
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs font-medium">
                                No hay campañas creadas todavía. Haz clic en "Nueva Campaña" para comenzar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL NUEVA CAMPAÑA (Alpine.js) -->
    <div x-show="openModal" class="fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs" style="display: none;" x-transition>
        <div @click.away="openModal = false" class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Crear Nueva Campaña de Discado</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Asigna parámetros de telefonía asociados a tu extensión y sube tu archivo CSV.</p>
                </div>
                <button @click="openModal = false" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form action="{{ route('campaigns.store') }}" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto space-y-5">
                @csrf
                <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                <!-- Inyectamos automáticamente la extensión del usuario actual -->
                <input type="hidden" name="extension" value="{{ auth()->user()->extension ?? '100' }}">

                <!-- Nombre de Campaña -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Nombre de la Campaña</label>
                    <input type="text" name="name" required placeholder="Ej. Campaña Ventas Marzo" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tipo de Campaña -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Tipo de Campaña</label>
                        <select name="campaign_type" x-model="campaignType" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-500">
                            <option value="queue">Con Agentes (Cola de Asterisk)</option>
                            <option value="broadcast">Masiva sin Agentes (Solo Audio)</option>
                        </select>
                    </div>

                    <!-- Canales Máximos -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Canales Simultáneos Máx.</label>
                        <input type="number" name="max_channels" value="5" min="1" max="50" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Condicional si es Queue -->
                    <div x-show="campaignType == 'queue'">
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Número de Cola (Asterisk)</label>
                        <input type="text" name="queue_number" placeholder="Ej. 8001" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Condicional si es Broadcast (Subida de audio directo) -->
                    <div x-show="campaignType == 'broadcast'" style="display: none;">
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Subir Audio para Broadcast (WAV / MP3)</label>
                        <input type="file" name="audio_file_upload" accept=".wav,.mp3" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50">
                    </div>
                </div>

                <!-- Opciones avanzadas para Cola (Intro Audio y DTMF) -->
                <div x-show="campaignType == 'queue'" class="p-4 bg-slate-50 rounded-2xl border border-slate-200/60 space-y-3" style="display: none;">
                    <h4 class="text-[11px] font-extrabold uppercase text-slate-700">Configuración Previa de Llamada (Intro)</h4>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Subir Audio de Introducción (Opcional)</label>
                        <input type="file" name="intro_audio_upload" accept=".wav,.mp3" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-200 rounded-xl bg-white">
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="requires_dtmf" id="requires_dtmf" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded-sm">
                        <label for="requires_dtmf" class="text-xs font-semibold text-slate-700">Exigir presionar tecla (DTMF) antes de transferir al agente</label>
                    </div>
                </div>

                <!-- Carga de Archivo CSV -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">Lista de Clientes (Archivo CSV)</label>
                    <input type="file" name="leads_file" accept=".csv, .txt" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-200 rounded-xl bg-slate-50">
                    <p class="text-[10px] text-slate-400 mt-1">Formato sugerido: Columna 1 = Nombre, Columna 2 = Teléfono.</p>
                </div>

                <!-- Footer del Modal -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-xs font-bold text-white hover:bg-indigo-700 shadow-sm transition-all">Guardar e Importar Leads</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function campaignManager() {
        return {
            openModal: false,
            campaignType: 'queue'
        }
    }
</script>
@endsection