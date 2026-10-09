@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full">
    
    <!-- Topbar Corporativo -->

@php
    $currentUser = auth()->user();
    $userExt = $currentUser ? \App\Models\UserExtension::where('user_id', $currentUser->id)->first() : null;
    $sipExtension = $userExt ? $userExt->extension_number : 'N/A';
@endphp

@include('layouts.topbar', [
    'showSoftphone' => true, 
    'title' => 'Configuración de Audio',
    'sipExtension' => $sipExtension
])
    <!-- Contenido Principal con Scroll -->
    <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Encabezado de Sección -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Dispositivos de Entrada y Salida</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Ajusta los micrófonos, audífonos y timbres para llamadas WebRTC en alta fidelidad.</p>
                    </div>
                </div>
            </div>

            <!-- Formulario de Audio WebRTC -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
                
                <!-- Entrada de Micrófono -->
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                        <i class="fa-solid fa-microphone text-slate-400 mr-1.5"></i> Micrófono (Entrada)
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                        <select id="audio-input-device" class="sm:col-span-2 w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-900 text-xs font-medium focus:ring-2 focus:ring-slate-900 outline-none">
                            <option value="">Cargando dispositivos...</option>
                        </select>
                        <button type="button" onclick="testMicrophone()" class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-vial"></i> Probar Micrófono
                        </button>
                    </div>
                    <!-- Barra de Nivel de Micrófono -->
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="mic-level-bar" class="bg-emerald-500 h-full w-0 transition-all duration-75"></div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Salida de Auriculares -->
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                        <i class="fa-solid fa-headphones text-slate-400 mr-1.5"></i> Auriculares (Voz de llamada)
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                        <select id="audio-output-device" class="sm:col-span-2 w-full px-4 py-2.5 rounded-xl border border-slate-200 text-slate-900 text-xs font-medium focus:ring-2 focus:ring-slate-900 outline-none">
                            <option value="">Predeterminado del Sistema</option>
                        </select>
                        <button type="button" onclick="testAudioTone()" class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-volume-high"></i> Tono de Prueba
                        </button>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Cancelación de Ruido y Eco -->
                <div class="space-y-3">
                    <span class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">Filtros de Procesamiento Digital (DSP)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-all">
                            <input type="checkbox" checked class="w-4 h-4 text-slate-900 rounded border-slate-300 focus:ring-slate-900">
                            <div>
                                <p class="text-xs font-bold text-slate-800">Cancelación de Eco</p>
                                <p class="text-[10px] text-slate-500">Evita el retorno de voz durante la llamada.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-all">
                            <input type="checkbox" checked class="w-4 h-4 text-slate-900 rounded border-slate-300 focus:ring-slate-900">
                            <div>
                                <p class="text-xs font-bold text-slate-800">Supresión de Ruido</p>
                                <p class="text-[10px] text-slate-500">Filtra el ruido ambiental de fondo.</p>
                            </div>
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Carga de dispositivos multimedia del navegador
    navigator.mediaDevices.enumerateDevices().then(devices => {
        const micSelect = document.getElementById('audio-input-device');
        const speakerSelect = document.getElementById('audio-output-device');
        micSelect.innerHTML = '';
        
        devices.forEach(device => {
            const option = document.createElement('option');
            option.value = device.deviceId;
            if (device.kind === 'audioinput') {
                option.text = device.label || `Micrófono ${micSelect.length + 1}`;
                micSelect.appendChild(option);
            } else if (device.kind === 'audiooutput') {
                option.text = device.label || `Altavoz ${speakerSelect.length + 1}`;
                speakerSelect.appendChild(option);
            }
        });
    });

    function testAudioTone() {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(440, audioCtx.currentTime);
        osc.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.5);
    }

    function testMicrophone() {
        navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
            const audioCtx = new AudioContext();
            const mic = audioCtx.createMediaStreamSource(stream);
            const analyser = audioCtx.createAnalyser();
            mic.connect(analyser);
            const data = new Uint8Array(analyser.frequencyBinCount);

            let count = 0;
            const interval = setInterval(() => {
                analyser.getByteFrequencyData(data);
                const avg = data.reduce((a, b) => a + b) / data.length;
                document.getElementById('mic-level-bar').style.width = Math.min(100, avg * 3) + '%';
                if (++count > 50) {
                    clearInterval(interval);
                    stream.getTracks().forEach(track => track.stop());
                    document.getElementById('mic-level-bar').style.width = '0%';
                }
            }, 50);
        });
    }
</script>
@endsection