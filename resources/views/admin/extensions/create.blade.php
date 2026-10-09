@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    
    <!-- HEADER / BARRA SUPERIOR DE ACCIÓN -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                <i class="fa-solid fa-network-wired text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                    Gestión de Extensiones PBX
                </h1>
                <p class="text-xs text-gray-500 font-medium">Provisionamiento y configuración de canales SIP / WebRTC en el motor Asterisk</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 shadow-sm transition-all">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Volver al Listado
            </a>
        </div>
    </div>

    <!-- ALERTA DINÁMICA DE RECARGA PENDIENTE (OCULTA POR DEFECTO) -->
    <div id="reloadContainer" class="hidden mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700 shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Cambios pendientes de aplicar</h4>
                <p class="text-xs text-amber-700">Se ha creado la extensión, pero el núcleo de Asterisk necesita recargar la configuración.</p>
            </div>
        </div>
        <button id="btnReloadAsterisk" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-md transition-all cursor-pointer">
            <i class="fa-solid fa-rotate animate-spin"></i> Aplicar Recarga Asterisk
        </button>
    </div>

    <!-- CONTENEDOR PRINCIPAL DEL FORMULARIO -->
    <div class="bg-white border border-gray-200 rounded-3xl shadow-xl overflow-hidden">
        
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-widest text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-plus text-blue-600 text-[10px]"></i> Nueva Extensión de Red
            </span>
            <span class="text-[11px] px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-semibold">
                Protocolo SIP / WebRTC
            </span>
        </div>

        <form id="formCrearSIP" class="p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- COLUMNA 1: CREDENCIALES Y RED -->
                <div class="space-y-5">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-800 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-blue-600"></i> Parámetros de Conexión
                    </h3>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Vincular a Usuario Local</label>
                        <select name="user_id" required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm">
                            <option value="">Seleccione un usuario...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Número de Extensión</label>
                        <input type="text" id="extensionInput" name="extension" required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-mono text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                            placeholder="Ej: 9000">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Usuario / Alias</label>
                            <input type="text" id="nameInput" name="name" required 
                                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                                placeholder="Ej: Operador 1">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Grupo / AccountCode</label>
                            <input type="text" name="accountcode" required 
                                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                                placeholder="Ej: G9010">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Contraseña SIP (Secret)</label>
                        <input type="text" name="password" required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-mono text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                            placeholder="••••••••••••">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tecnología / Tipo de Extensión</label>
                        <select name="tipo" required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm">
                            <option value="sip">Dispositivo SIP genérico - Port:5060</option>
                            <option value="pjsip">Dispositivo PJSIP genérico - Port:5066</option>
                            <option value="webrtc">Dispositivo WebRTC SIP (Para FOP2 / Web)</option>
                        </select>
                    </div>
                </div>

                <!-- COLUMNA 2: CONFIGURACIÓN DE LLAMADAS Y BUZÓN -->
                <div class="space-y-5">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-800 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-blue-600"></i> Políticas de Enrutamiento
                    </h3>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Correo de Voz (Voicemail)</label>
                        <input type="email" name="voicemail" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                            placeholder="usuario@dominio.com">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Ring Timer (Segundos de Ring)</label>
                        <input type="number" name="ringtimer" value="0" min="0" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Destino si no contesta (No Answer)</label>
                        <input type="text" name="noanswer_dest" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                            placeholder="Opcional (Ej: IVR o Extensión)">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Destino si está ocupado (Busy)</label>
                        <input type="text" name="busy_dest" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all shadow-sm" 
                            placeholder="Opcional">
                    </div>
                </div>

            </div>

            <!-- BOTÓN DE ACCIÓN FINAL -->
            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 transition-all cursor-pointer active:scale-95">
                    <i class="fa-solid fa-server"></i> Registrar Extensión en Issabel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPTS DE PETICIÓN ASÍNCRONA -->
<!-- SCRIPTS DE PETICIÓN ASÍNCRONA -->
<script>
document.getElementById('formCrearSIP').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    Swal.fire({
        title: 'Procesando conexión...',
        text: 'Enviando parámetros a la API de Issabel...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch("{{ route('admin.extensions.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const text = await res.text();
        let data;

        try {
            data = JSON.parse(text.trim());
        } catch (err) {
            data = { status: 'error', mensaje: 'Respuesta ilegible devuelta por el servidor Laravel.' };
        }

        if (data.status === 'error') {
            Swal.fire({ title: 'Error de Registro', text: data.mensaje, icon: 'error' });
        } else {
            Swal.fire({ title: '¡Operación Exitosa!', text: data.mensaje || 'Extensión creada correctamente', icon: 'success' });
            e.target.reset();
            document.getElementById('reloadContainer').classList.remove('hidden');
        }

    } catch (err) {
        Swal.fire({ title: 'Error de Red', text: 'No se pudo establecer comunicación con el controlador.', icon: 'error' });
    }
});

document.getElementById('btnReloadAsterisk').addEventListener('click', async () => {
    const reloadContainer = document.getElementById('reloadContainer');

    Swal.fire({ title: 'Recargando Core...', text: 'Solicitando recarga a la API de Issabel...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const res = await fetch("{{ route('admin.extensions.reload') }}", { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();

        // Aceptamos tanto 'ok' como 'success' para asegurar que oculte la alerta sin fallos
        if (data.status === 'ok' || data.status === 'success') {
            Swal.fire({ title: '¡Core Recargado!', text: data.mensaje, icon: 'success' });
            if (reloadContainer) reloadContainer.classList.add('hidden');
        } else {
            Swal.fire({ title: 'Error al recargar', text: data.mensaje, icon: 'error' });
        }
    } catch (err) {
        Swal.fire({ title: 'Error de Red', text: 'No se pudo completar la solicitud de recarga.', icon: 'error' });
    }
});

// Autocompletar nombre con extensión de manera inteligente
const extensionInput = document.getElementById('extensionInput');
const nameInput = document.getElementById('nameInput');
extensionInput.addEventListener('input', () => {
    if (!nameInput.dataset.touched) {
        nameInput.value = extensionInput.value;
    }
});
nameInput.addEventListener('input', () => {
    nameInput.dataset.touched = true;
});
</script>
@endsection