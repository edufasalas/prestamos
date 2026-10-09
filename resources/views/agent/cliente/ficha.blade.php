@extends('layouts.app')


@section('content')
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Ficha del Cliente / CRM'])


<style>
    /* Scroll fino y profesional tipo Mac / Aplicación moderna */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    [x-cloak] {
        display: none !important;
    }
</style>
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">

    <!-- Botón de Retorno -->
    <div>
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 px-3.5 py-2 rounded-xl shadow-xs transition-all">
            <i class="fa-solid fa-arrow-left"></i> Volver al Historial
        </a>
    </div>

    @if(session('success'))
        <div class="bg-slate-900 text-white px-4 py-3 rounded-xl text-xs font-bold tracking-wide shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
    <!-- Columna Izquierda: Datos del Contacto -->
        <div class="bg-white p-6 rounded-2xl border border-slate-300 shadow-xs space-y-5 lg:col-span-1" x-data="webRtcSoftphone()">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Contacto</h3>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                    {{ $cliente->estado_crm ?? 'Prospecto' }}
                </span>
                
                {{-- Botón Click-to-Call Circular Corporativo --}}
                <button @click="iniciarLlamada('{{ $cliente->telefono ?? '' }}', '{{ $cliente->nombre ?? 'Cliente' }}')" 
                        type="button" 
                        title="Llamar a {{ $cliente->nombre ?? 'Cliente' }}"
                        class="group relative inline-flex items-center justify-center p-2.5 bg-[#121216] hover:bg-emerald-500 border border-emerald-500/30 hover:border-emerald-500/60 text-slate-100 rounded-full shadow-xl shadow-black/40 hover:shadow-emerald-500/20 active:scale-95 transition-all duration-300 cursor-pointer overflow-hidden">
                    
                    {{-- Efecto de brillo sutil al hacer hover --}}
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-emerald-500/10 to-teal-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                
                    {{-- Icono de Teléfono con contenedor circular y micro-animación --}}
                    <div class="relative flex items-center justify-center text-emerald-400 group-hover:text-black transition-colors duration-300">
                        <svg class="w-4 h-4 transform group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                </button>    
                
                {{-- Botón para Abrir Modal de Correo --}}
                <button @click="$dispatch('abrir-modal-correo')" 
                        type="button" 
                        title="Enviar Correo"
                        class="group relative inline-flex items-center justify-center p-2.5 bg-[#121216] hover:bg-slate-900 border border-emerald-500/30 hover:border-emerald-500/60 text-slate-100 rounded-full shadow-xl shadow-black/40 hover:shadow-emerald-500/20 active:scale-95 transition-all duration-300 cursor-pointer overflow-hidden">
                    
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-emerald-500/10 to-teal-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                
                    <div class="relative flex items-center justify-center text-emerald-400 group-hover:text-black transition-colors duration-300">
                        <svg class="w-4 h-4 transform group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </button>             
            </div>

            <!-- Tus inputs de formulario anteriores van aquí... -->
            <form action="{{ route('agent.cliente.update', $cliente->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" value="{{ $cliente->nombre }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none focus:border-slate-900" required>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Teléfono Principal</label>
                    <input type="text" value="{{ $cliente->telefono }}" class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-600 cursor-not-allowed" disabled>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Correo Electrónico</label>
                    <input type="email" name="correo" value="{{ $cliente->correo }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none focus:border-slate-900" placeholder="correo@ejemplo.com">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Empresa / Organización</label>
                    <input type="text" name="empresa" value="{{ $cliente->empresa }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none focus:border-slate-900" placeholder="Nombre de empresa">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ $cliente->direccion }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none focus:border-slate-900" placeholder="Dirección física">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Estado en el CRM</label>
                    <select name="estado_crm" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none focus:border-slate-900">
                        <option value="Prospecto" {{ $cliente->estado_crm == 'Prospecto' ? 'selected' : '' }}>Prospecto</option>
                        <option value="Interesado" {{ $cliente->estado_crm == 'Interesado' ? 'selected' : '' }}>Interesado</option>
                        <option value="Cliente Activo" {{ $cliente->estado_crm == 'Cliente Activo' ? 'selected' : '' }}>Cliente Activo</option>
                        <option value="No Interesado" {{ $cliente->estado_crm == 'No Interesado' ? 'selected' : '' }}>No Interesado</option>
                        <option value="Seguimiento" {{ $cliente->estado_crm == 'Seguimiento' ? 'selected' : '' }}>Seguimiento Pendiente</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider py-2.5 rounded-xl transition-all shadow-sm cursor-pointer">
                    Actualizar Ficha
                </button>
            </form>

            {{-- MODAL SOFTPHONE ESTILO CENTRALITA TELEFÓNICA (CON ANIMACIONES REALISTAS) --}}
            <div x-cloak x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
                <div @click.away="cerrarModal()" class="bg-slate-900 rounded-[2.5rem] shadow-2xl max-w-sm w-full overflow-hidden border border-slate-800 flex flex-col transform transition-all text-white">
                    
                    {{-- Barra Superior Tipo Smartphone / Softphone --}}
                    <div class="px-6 pt-6 pb-4 flex justify-between items-center border-b border-slate-800/80 bg-slate-900/50">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :class="enLlamada ? 'bg-emerald-500 animate-pulse' : (conectado ? 'bg-blue-500 animate-ping' : 'bg-amber-500 animate-pulse')"></span>
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400" x-text="enLlamada ? 'Llamada Segura' : 'Central VoIP'"></span>
                        </div>
                        <button @click="cerrarModal()" type="button" class="text-slate-400 hover:text-white w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-sm font-bold transition">&times;</button>
                    </div>

                    {{-- Pantalla Principal del Llamador (Avatar y Datos) --}}
                    <div class="p-8 text-center relative flex flex-col items-center justify-center bg-gradient-to-b from-emerald-900 to-emerald-950">
                        
                        <!-- Círculo Avatar con Animación de Ondas (Simulando Timbre Telefónico Real) -->
                        <div class="relative mb-5 flex items-center justify-center">
                            <!-- Ondas expansivas si está timbrando o conectando -->
                            <div x-show="!enLlamada" class="absolute w-28 h-28 rounded-full bg-blue-500/20 animate-ping"></div>
                            <div x-show="enLlamada" class="absolute w-28 h-28 rounded-full bg-emerald-500/20 animate-ping"></div>

                            <!-- Contenedor del Icono de Teléfono / Agente -->
                            <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-slate-800 to-slate-700 border-2 border-slate-700/60 shadow-xl flex items-center justify-center relative z-10 text-white">
                                <!-- Spinner si marca -->
                                <svg x-show="!enLlamada" class="w-9 h-9 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <!-- Icono de llamada activa -->
                                <svg x-show="enLlamada" class="w-9 h-9 text-emerald-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Nombre y Teléfono del Cliente -->
                        <h3 class="text-lg font-bold tracking-tight text-white mb-1" x-text="nombreCliente"></h3>
                        <p class="text-sm font-mono text-slate-400 tracking-wider" x-text="telefonoDestino"></p>
                    </div>

                    {{-- Tarjeta de Estado Dinámico (Conectando / Timbrando / En Curso) --}}
                    <div class="px-6 pb-6">
                        <div class="bg-slate-800/60 backdrop-blur-md p-4 rounded-2xl border border-slate-700/50 text-center space-y-1 mb-6 shadow-inner">
                            <p class="text-sm font-bold tracking-wide" :class="enLlamada ? 'text-emerald-400' : 'text-blue-400'" x-text="estadoLlamada">Conectando...</p>
                            <p class="text-xs text-slate-400 font-medium" x-text="subEstado">Estableciendo canal de voz...</p>
                        </div>

                        <audio id="remoteAudio" autoplay></audio>

                        {{-- Botón de Colgar Central Estilo Teléfono Móvil / Enterprise --}}
                        <div class="flex justify-center items-center pb-2">
                            <button @click="colgar()" type="button" class="w-16 h-16 rounded-full bg-rose-600 hover:bg-rose-500 active:scale-95 text-white shadow-lg shadow-rose-600/40 flex items-center justify-center transition-all duration-200 group cursor-pointer" title="Finalizar llamada">
                                <svg class="w-7 h-7 transform group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.516l2.257-1.13a1 1 0 00.502-1.21L8.228 3.684A1 1 0 007.28 3H5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-950 px-6 py-3 text-center border-t border-slate-800/80">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Enterprise WebRTC Softphone</span>
                    </div>

                </div>
            </div>
            
            {{-- MODAL 3: Envío Rápido de Correo por Cliente (Alpine.js) --}}
            <div x-data="{ openEmailModal: false }">
            
                {{-- Escucha el evento del botón para abrir el modal --}}
                <div x-cloak x-show="openEmailModal" 
                     @abrir-modal-correo.window="openEmailModal = true"
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                    
                    <div @click.away="openEmailModal = false" class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 transform transition-all">
                        
                        {{-- Cabecera del Modal --}}
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Enviar Correo a {{ $cliente->nombre }}
                            </h2>
                            <button @click="openEmailModal = false" type="button" class="text-slate-400 hover:text-slate-600 text-2xl font-bold leading-none">&times;</button>
                        </div>
            
                        {{-- Cuerpo del Modal (Tu formulario exacto) --}}
                        <div class="p-6">
                            @php
                                $userCompanyId = Auth::user()->company_id ?? null;
                                $templates = \Illuminate\Support\Facades\DB::table('templates')
                                    ->when($userCompanyId, function($q) use ($userCompanyId) {
                                        $q->where('company_id', $userCompanyId);
                                    })
                                    ->get();
                            @endphp
            
                            <form action="{{ route('admin.envios.enviar') }}" method="POST" class="space-y-5">
                                @csrf
                                
                                {{-- ID del cliente fijo obtenido de la ficha actual --}}
                                <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
            
                                <div>
                                    <label for="template_id" class="block text-sm font-medium text-slate-700 mb-1">Seleccionar Plantilla:</label>
                                    <select name="template_id" id="template_id" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">-- Seleccione una plantilla --</option>
                                        @foreach($templates as $tmp)
                                            <option value="{{ $tmp->id }}">{{ $tmp->titulo }} (Asunto: {{ $tmp->asunto }})</option>
                                        @endforeach
                                    </select>
                                </div>
            
                                <div class="p-3 bg-sky-50 border border-sky-100 rounded-lg text-xs text-sky-800 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-sky-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Variables permitidas: <strong>{nombre}</strong> y <strong>{telefono}</strong>.</span>
                                </div>
            
                                <div class="flex justify-end gap-3 pt-2">
                                    <button @click="openEmailModal = false" type="button" class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg text-sm transition">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="py-2.5 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition shadow-sm">
                                        Enviar Correo Ahora
                                    </button>
                                </div>
                            </form>
                        </div>
            
                    </div>
                </div>
            </div> 
        </div>

        <!-- Columna Derecha: Historial de Notas (Timeline) y Nueva Nota -->
        <div class="bg-white p-6 rounded-2xl border border-slate-300 shadow-sm space-y-6 lg:col-span-2 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                        Historial de Interacciones y Notas
                    </h3>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        {{ count($notas) }} {{ count($notas) == 1 ? 'Nota' : 'Notas' }}
                    </span>
                </div>

                <!-- Contenedor del Historial con Scroll Profesional -->
                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-3 custom-scrollbar">
                    @forelse($notas as $nota)
                        <div class="bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 hover:border-slate-300 p-4 rounded-xl space-y-2.5 transition-all">
                            <div class="flex items-center justify-between text-[11px] text-slate-600 font-semibold">
                                <span class="flex items-center gap-1 text-slate-700">
                                    <i class="fa-solid fa-clock text-slate-400"></i> {{ \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y H:i') }}
                                </span>
                                <span class="bg-slate-200/70 text-slate-700 font-medium px-2 py-0.5 rounded-md text-[10px] tracking-wide">
                                    Agente ID: {{ $nota->user_id ?? 'Sistema' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-900 font-medium whitespace-pre-line leading-relaxed pl-1 border-l-2 border-slate-300">
                                {{ $nota->nota }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-12 text-slate-400 italic text-xs bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                            <i class="fa-regular fa-folder-open text-2xl mb-2 block text-slate-300"></i>
                            No hay notas registradas para este cliente todavía.
                        </div>
                    @endforelse
                </div>
            </div>

            <form action="{{ route('agent.cliente.nota.store', $cliente->id) }}" method="POST" class="space-y-3 border-t border-slate-200 pt-4 mt-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Agregar Nueva Nota de Llamada</label>
                    <textarea name="nota" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-900 transition-colors" placeholder="Escribe los detalles importantes de la conversación con el cliente..." required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-slate-900 hover:bg-black text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl transition-all shadow-sm cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Nota
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@push('scripts')
{{-- Script de JsSIP --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function webRtcSoftphone() {
    let ua = null;
    let session = null;
    const sipHost = "servidores.voiparkas.com";
    let ringbackAudio = null;

    return {
        isOpen: false,
        conectado: false,
        enLlamada: false,
        telefonoDestino: '',
        nombreCliente: '',
        estadoLlamada: 'Inicializando...',
        subEstado: 'Esperando conexión SIP...',

        iniciarLlamada(telefono, nombre) {
            if (!telefono) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Número no disponible',
                    text: 'El cliente no cuenta con un número telefónico registrado.',
                    confirmButtonColor: '#0f172a'
                });
                return;
            }

            this.telefonoDestino = telefono;
            this.nombreCliente = nombre;
            this.isOpen = true;
            this.estadoLlamada = 'Autenticando extensión...';
            this.subEstado = 'Obteniendo credenciales SIP...';
            this.enLlamada = false;
            this.conectado = false;

            // Audio de timbre falso en bucle
            if (!ringbackAudio) {
                ringbackAudio = new Audio('https://servidores.voiparkas.com/cdrweb/c2c/ringing.wav');
                ringbackAudio.loop = true;
            }

            fetch('{{ route("agent.softphone.token") }}')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        this.conectarJsSIP(data.extension, data.secret, telefono);
                    } else {
                        this.estadoLlamada = 'Error de autorización';
                        this.subEstado = data.error;
                    }
                })
                .catch(err => {
                    this.estadoLlamada = 'Error de red';
                    this.subEstado = 'No se pudo conectar con el servidor backend.';
                });
        },

        conectarJsSIP(extension, secret, telefono) {
            this.estadoLlamada = 'Conectando al servidor VoIP...';
            
            let socket = new JsSIP.WebSocketInterface('wss://' + sipHost + ':8089/ws');
            
            let configuration = {
                sockets: [socket],
                uri: "sip:" + extension + "@" + sipHost,
                password: secret,
                session_timers: false
            };

            try {
                ua = new JsSIP.UA(configuration);

                ua.on('registered', (e) => {
                    this.conectado = true;
                    this.estadoLlamada = 'Marcando al cliente...';
                    this.subEstado = 'Estableciendo canal de voz...';
                    
                    if (ringbackAudio) {
                        ringbackAudio.play().catch(e => console.log("Autoplay restringido por el navegador", e));
                    }

                    this.realizarLlamadaSIP(telefono);
                });

                ua.on('registrationFailed', (e) => {
                    this.estadoLlamada = 'Fallo de registro SIP';
                    this.subEstado = 'Verifica la extensión o contraseña.';
                    this.detenerTimbreFalso();
                });

                ua.start();
            } catch (error) {
                this.estadoLlamada = 'Error en WebRTC';
                this.subEstado = error.message;
                this.detenerTimbreFalso();
            }
        },

        realizarLlamadaSIP(dest) {
            const target = `sip:${dest}@${sipHost}`;
            
            session = ua.call(target, {
                mediaConstraints: { audio: true, video: false },
                rtcOfferConstraints: { offerToReceiveAudio: true }
            });

            session.on('progress', () => {
                this.estadoLlamada = 'Timbrando en destino...';
                this.subEstado = 'Esperando que el cliente responda.';
            });

            session.on('accepted', () => {
                this.detenerTimbreFalso();
                this.enLlamada = true;
                this.estadoLlamada = '¡Llamada en curso!';
                this.subEstado = 'Comunicación segura establecida.';
            });

            session.on('failed', (e) => {
                this.detenerTimbreFalso();
                this.enLlamada = false;
                this.estadoLlamada = 'Llamada fallida';
                this.subEstado = 'Causa: ' + (e.cause || 'Desconocida');
                this.notificarFinLlamada('La llamada no pudo completarse.');
            });

            session.on('ended', () => {
                this.detenerTimbreFalso();
                this.enLlamada = false;
                this.estadoLlamada = 'Llamada finalizada';
                this.subEstado = 'La llamada ha concluido.';
                this.notificarFinLlamada('La llamada ha finalizado con éxito.');
            });

            if (session.connection) {
                session.connection.addEventListener('track', (e) => {
                    if (e.track.kind === 'audio') {
                        let audioElement = document.getElementById('remoteAudio');
                        if (audioElement) {
                            audioElement.srcObject = e.streams[0];
                        }
                    }
                });
            }
        },

        detenerTimbreFalso() {
            if (ringbackAudio) {
                ringbackAudio.pause();
                ringbackAudio.currentTime = 0;
            }
        },

        notificarFinLlamada(mensaje) {
            this.cerrarModal();
            
            // SweetAlert2 Profesional de Resumen Post-Llamada
            Swal.fire({
                title: '📞 Llamada Concluida',
                text: mensaje + ' ¿Desea registrar las observaciones o crear su reporte de CRM ahora?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#059669', // Esmeralda corporativo
                cancelButtonColor: '#64748b',   // Slate
                confirmButtonText: '<i class="fas fa-clipboard-list mr-1"></i> Crear Reporte CRM',
                cancelButtonText: 'Cerrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí puedes enfocar el formulario o redirigir si lo requieres
                    const primerInput = document.querySelector('input[name="nota"]');
                    if (primerInput) primerInput.focus();
                }
            });
        },

        colgar() {
            this.detenerTimbreFalso();
            if (session) {
                session.terminate();
            }
            // El evento 'ended' disparará automáticamente la notificación SweetAlert
        },

        cerrarModal() {
            this.detenerTimbreFalso();
            if (session && this.enLlamada) {
                session.terminate();
            }
            if (ua) {
                ua.stop();
            }
            this.isOpen = false;
            this.enLlamada = false;
            this.conectado = false;
        }
    }
}
</script>
@endpush
@endsection