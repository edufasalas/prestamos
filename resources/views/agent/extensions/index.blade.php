@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full">
    
    <!-- Topbar Corporativo Reutilizable -->
    @include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Configuración de Extensión SIP'])

    <!-- Contenido Principal con Scroll -->
    <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
        
        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Encabezado corporativo de la sección -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Provisión de Extensión WebRTC</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Gestión de credenciales de conexión e integración en tiempo real con Issabel / Asterisk PBX.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> PBX Activo
                    </span>
                </div>
            </div>

            <!-- Notificación de éxito -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Formulario Principal -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Parámetros SIP / WSS</h2>
                        <span class="text-[10px] font-bold text-slate-500 bg-slate-200/60 px-2 py-0.5 rounded">TLS / WSS Encriptado</span>
                    </div>

                    <form action="{{ route('agent.extensions.store') }}" method="POST" class="p-6 space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Número de extensión -->
                            <div class="space-y-1.5">
                                <label for="extension_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Extensión SIP <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                        <i class="fa-solid fa-hashtag text-xs"></i>
                                    </span>
                                    <input type="text" name="extension_number" id="extension_number" 
                                           value="{{ old('extension_number', $extension->extension_number ?? '') }}" 
                                           required placeholder="Ej. 1010"
                                           oninput="updatePreview()"
                                           class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-slate-900 text-xs font-semibold focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all placeholder:text-slate-300">
                                </div>
                                @error('extension_number')
                                    <p class="text-rose-500 text-[10px] font-semibold flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contraseña SIP -->
                            <div class="space-y-1.5">
                                <label for="sip_secret" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Secreto SIP (sip_secret) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                        <i class="fa-solid fa-key text-xs"></i>
                                    </span>
                                    <input type="password" name="sip_secret" id="sip_secret" 
                                           value="{{ old('sip_secret', $extension->sip_secret ?? '') }}" 
                                           required placeholder="••••••••"
                                           class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-200 text-slate-900 text-xs font-semibold focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all">
                                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                        <i id="toggle-icon" class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                                @error('sip_secret')
                                    <p class="text-rose-500 text-[10px] font-semibold flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- URL del WebSocket -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center">
                                <label for="websocket_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Ruta del WebSocket WSS <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" onclick="testWssConnection()" class="text-[11px] font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition-colors">
                                    <i class="fa-solid fa-plug text-[10px]"></i> Probar Conexión
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                    <i class="fa-solid fa-network-wired text-xs"></i>
                                </span>
                                <input type="text" name="websocket_url" id="websocket_url" 
                                       value="{{ old('websocket_url', $extension->websocket_url ?? 'wss://tu-ip-issabel:8089/ws') }}" 
                                       required placeholder="wss://servidor:8089/ws"
                                       oninput="updatePreview()"
                                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-slate-900 text-xs font-mono font-medium focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all placeholder:text-slate-300">
                            </div>
                            <div id="wss-status-msg" class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-1">
                                <i class="fa-solid fa-circle-info text-slate-400"></i> Puerto WebRTC predeterminado de Asterisk/Issabel: <code class="font-bold text-slate-600">8089/ws</code>
                            </div>
                            @error('websocket_url')
                                <p class="text-rose-500 text-[10px] font-semibold flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center justify-between pt-5 border-t border-slate-100">
                            <span class="text-[11px] text-slate-400 font-medium">Los cambios surten efecto de inmediato en la sesión activa.</span>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Guardar Extensión
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Panel Lateral: Monitor de Registro en Tiempo Real -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-desktop text-slate-400"></i> Vista Previa del Endpoint
                        </h3>
                        
                        <div class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-[11px] space-y-2 border border-slate-800">
                            <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                                <span class="text-slate-500">PARAMETRO</span>
                                <span class="text-slate-500">VALOR</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">SIP User:</span>
                                <span id="preview-user" class="text-emerald-400 font-bold">{{ $extension->extension_number ?? '---' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">WS Target:</span>
                                <span id="preview-ws" class="text-sky-400 font-bold truncate max-w-[130px]">{{ $extension->websocket_url ?? '---' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Transporte:</span>
                                <span class="text-amber-400 font-bold">WSS (Secure)</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60 space-y-2">
                            <span class="text-[11px] font-bold text-slate-700 block">Requisitos Previos de Issabel:</span>
                            <ul class="text-[11px] text-slate-500 space-y-1 list-disc list-inside">
                                <li>Habilitar soporte <code class="text-slate-700 bg-slate-200/60 px-1 rounded">http.conf</code> (port 8089).</li>
                                <li>Módulo <code class="text-slate-700 bg-slate-200/60 px-1 rounded">res_http_websocket</code> cargado.</li>
                                <li>Certificado SSL HTTPS válido instalado.</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('sip_secret');
        const icon = document.getElementById('toggle-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function updatePreview() {
        const num = document.getElementById('extension_number').value;
        const ws = document.getElementById('websocket_url').value;
        
        document.getElementById('preview-user').textContent = num || '---';
        document.getElementById('preview-ws').textContent = ws || '---';
    }

    function testWssConnection() {
        const url = document.getElementById('websocket_url').value;
        const msg = document.getElementById('wss-status-msg');
        
        if (!url.startsWith('wss://')) {
            msg.innerHTML = '<span class="text-rose-500 font-bold"><i class="fa-solid fa-circle-xmark"></i> La URL debe comenzar con wss://</span>';
            return;
        }

        msg.innerHTML = '<span class="text-amber-600 font-bold"><i class="fa-solid fa-spinner animate-spin"></i> Intentando handshaking con ' + url + '...</span>';

        try {
            const socket = new WebSocket(url, 'sip');
            const timer = setTimeout(() => {
                socket.close();
                msg.innerHTML = '<span class="text-rose-500 font-bold"><i class="fa-solid fa-circle-xmark"></i> Tiempo de espera agotado o puerto 8089 bloqueado</span>';
            }, 4000);

            socket.onopen = function() {
                clearTimeout(timer);
                socket.close();
                msg.innerHTML = '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> ¡Conexión Socket WSS Exitosa!</span>';
            };

            socket.onerror = function() {
                clearTimeout(timer);
                msg.innerHTML = '<span class="text-rose-500 font-bold"><i class="fa-solid fa-triangle-exclamation"></i> Error al conectar con WebSocket (Verificar SSL o Firewall)</span>';
            };
        } catch (e) {
            msg.innerHTML = '<span class="text-rose-500 font-bold"><i class="fa-solid fa-circle-xmark"></i> URL no válida</span>';
        }
    }
</script>
@endsection