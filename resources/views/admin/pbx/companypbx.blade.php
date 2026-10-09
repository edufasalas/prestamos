@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-100 flex flex-col antialiased selection:bg-slate-800 selection:text-white"
     x-data="companyPbxData()" 
     x-init="initDashboard()">
    
    <!-- Topbar Corporativo -->
    @php
        $currentUser = auth()->user();
        $userExt = $currentUser ? \App\Models\UserExtension::where('user_id', $currentUser->id)->first() : null;
        $sipExtension = $userExt ? $userExt->extension_number : 'N/A';
    @endphp

    @include('layouts.topbar', [
        'showSoftphone' => true, 
        'title' => 'Centralita Telefónica - ' . $company->name,
        'sipExtension' => $sipExtension
    ])

    <!-- Contenedor Principal Institucional -->
    <main class="flex-1 p-6 lg:p-8 max-w-7xl mx-auto w-full space-y-6">
        <!-- ========================================= -->
        <!-- CABECERA DE EMPRESA -->
        <!-- ========================================= -->
        <div class="bg-gradient-to-r from-white via-white to-indigo-50/30 p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <!-- Icono PBX -->
                <div class="w-16 h-16 rounded-2xl text-white flex items-center justify-center text-3xl shrink-0
                            shadow-md shadow-indigo-200 border border-indigo-400/30" style="background-color: {{ $company->primary_color ?? '#2052F7' }}">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl lg:text-2xl font-normal
                                   text-slate-900 tracking-tight">
                            {{ $company->name }}
                        </h1>
                        <!-- Estado Empresa -->
                        <span class="inline-flex items-center gap-1.5
                                   px-3 py-1 text-[11px]
                                   font-normal rounded-full uppercase
                                   tracking-wider
                                   bg-emerald-50 text-emerald-700
                                   border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $company->status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Monitoreo y telemetría de infraestructura y extensiones SIP en tiempo real.
                    </p>
                </div>
            </div>
            <!-- Estado PBX -->
            <div class="flex items-center gap-2.5 px-4 py-2 bg-emerald-50/80 rounded-xl border border-emerald-200/60 text-xs text-emerald-700 shadow-xs">
                <span class="relative flex w-2.5 h-2.5">
                    <span class="absolute inline-flex w-full h-full
                                   rounded-full bg-emerald-400
                                   opacity-60 animate-ping">
                    </span>
                    <span class="relative inline-flex w-2.5 h-2.5
                                   rounded-full bg-emerald-500">
                    </span>
                </span>
                <span class="uppercase tracking-wider text-[11px] font-normal">
                    PBX Conectado
                </span>
            </div>
        </div>
        <!-- ========================================= -->
        <!-- TARJETAS DE MÉTRICAS -->
        <!-- ========================================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- DID Principal -->
            <div class="bg-white p-5 rounded-2xl border transition-all duration-300 shadow-sm flex items-center justify-between gap-4"
                 :class="{
                     'border-amber-300 bg-amber-50/40 shadow-md animate-pulse': activeDidCall, 
                     'border-slate-200/80 hover:border-blue-200 hover:shadow-md': !activeDidCall 
                 }">
                
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shrink-0 border shadow-xs transition-colors relative overflow-hidden"
                         :class="{
                             'bg-amber-100 text-amber-700 border-amber-200 shadow-amber-100': activeDidCall,
                             'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-50': !activeDidCall && '{{ $company->incoming_did }}',
                             'bg-rose-50 text-rose-500 border-rose-100 shadow-rose-50': !activeDidCall && !'{{ $company->incoming_did }}'
                         }">
                        <i class="fa-solid" :class="{
                            'fa-phone-volume animate-bounce': activeDidCall,
                            'fa-globe text-indigo-600': !activeDidCall && '{{ $company->incoming_did }}',
                            'fa-ban text-rose-500': !activeDidCall && !'{{ $company->incoming_did }}'
                        }"></i>
                    </div>
            
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                                  :class="activeDidCall ? 'bg-amber-200 text-amber-800' : 'bg-indigo-50 text-indigo-700 border border-indigo-100'"
                                  x-text="activeDidCall ? 'Entrante' : 'DID Virtual'">
                            </span>
                            
                        </div>
            
                        <div class="mt-1">
                            <span class="text-lg tracking-tight block font-mono font-bold"
                                  :class="'{{ $company->incoming_did }}' ? 'text-slate-900' : 'text-rose-600 text-sm font-sans font-semibold'"
                                  x-show="!activeDidCall">
                                {{ $company->incoming_did ?? 'No asignado' }}
                            </span>
                        </div>
            
                        <div class="mt-0.5" x-show="activeDidCall" x-cloak>
                            <span class="text-xs text-slate-500 block">De: <strong class="text-slate-900" x-text="activeDidCall?.caller_id"></strong></span>
                        </div>
                    </div>
                </div>
            
                <div class="text-right" x-show="activeDidCall" x-cloak>
                    <span class="text-[10px] uppercase tracking-wider text-amber-600 block font-semibold">En curso</span>
                    <span class="text-sm font-mono font-bold text-amber-800" x-text="formatTime(activeDidTimer)"></span>
                </div>
            </div>
        
            <!-- IVR (Se anima inteligentemente SOLO cuando hay una llamada activa en el sistema) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-5 transition-all duration-200 hover:border-purple-200 hover:shadow-md"
                 :class="{ 'border-emerald-300 bg-emerald-50/20 shadow-sm': activeDidCall && {{ $company->has_ivr ? 'true' : 'false' }} }">
                 
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl shrink-0 border shadow-xs relative overflow-hidden transition-colors"
                     :class="{
                         @if($company->has_ivr)
                             'bg-emerald-50 text-emerald-600 border-emerald-100': !activeDidCall,
                             'bg-emerald-100 text-emerald-700 border-emerald-300 shadow-md': activeDidCall
                         @else
                             'bg-rose-50 text-rose-500 border-rose-100': true
                         @endif
                     }">
                    
                    <!-- Efecto de luz/pulso pasando por el icono SOLO cuando hay llamada activa y el IVR está activo -->
                    @if($company->has_ivr)
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/60 to-transparent"
                             :class="{ 'animate-pulse': activeDidCall }"
                             x-show="activeDidCall" x-cloak></div>
                    @endif
        
                    <!-- Icono estático por defecto, con pulso elegante solo durante la llamada -->
                    <i class="fa-solid fa-network-wired relative z-10" 
                       :class="{ 'animate-pulse text-emerald-700': activeDidCall && {{ $company->has_ivr ? 'true' : 'false' }} }"></i>
                </div>
                
                <div>
                    <span class="text-[11px] text-slate-400 uppercase tracking-wider block font-semibold">
                        Menú IVR
                    </span>
                    <span class="text-base mt-0.5 block font-medium
                           {{ $company->has_ivr ? 'text-emerald-700' : 'text-rose-600' }}">
                        {{ $company->has_ivr ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        
            <!-- Canales -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-5 transition-all duration-200 hover:border-amber-200 hover:shadow-md">
                <div class="w-20 h-20 rounded-2xl
                            bg-amber-50 text-amber-600
                            border border-amber-100
                            flex items-center justify-center
                            text-3xl shrink-0
                            shadow-xs shadow-amber-50">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 uppercase tracking-wider block font-semibold">
                        Canales simultáneos
                    </span>
                    <span class="text-base text-slate-800 mt-0.5 block font-medium">
                        {{ $company->max_concurrent_calls ?? 5 }} Canales
                    </span>
                </div>
            </div>
        
            <!-- Minutos -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-5 transition-all duration-200 hover:border-emerald-200 hover:shadow-md">
                <div class="w-20 h-20 rounded-2xl
                            bg-emerald-50 text-emerald-600
                            border border-emerald-100
                            flex items-center justify-center
                            text-3xl shrink-0
                            shadow-xs shadow-emerald-50">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 uppercase tracking-wider block font-semibold">
                        Bolsa de Minutos
                    </span>
                    <span class="text-base text-slate-800 mt-0.5 block font-medium">
                        {{ $company->minutes_limit ?? 0 }} min
                    </span>
                </div>
            </div>
        </div>
        <!-- ========================================= -->
        <!-- PANEL DE EXTENSIONES -->
        <!-- ========================================= -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-6">
            <!-- Encabezado -->
            <div class="flex items-center justify-between pb-5 border-b border-slate-200">
                <div>
                    <h2 class="text-base lg:text-lg text-slate-900 font-semibold flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center">
                            <i class="fa-solid fa-headset text-slate-700 text-lg"></i>
                        </div>
                        <div>
                            <span>Estado de Extensiones SIP</span>
                            <p class="text-xs text-slate-500 font-normal mt-0.5">
                                Dispositivos registrados en tiempo real
                            </p>
                        </div>
                    </h2>
                </div>
                <span
                    class="text-xs font-semibold text-slate-600 bg-slate-50 border border-slate-200
                           px-3 py-2 rounded-full"
                    x-text="Object.keys(extensions).length + ' Registradas'">
                </span>
            </div>
            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <template x-for="(extData, extNum) in extensions" :key="extNum">
                    <div class="group relative overflow-hidden rounded-2xl border bg-white p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                        :class="{ 'border-emerald-100 hover:border-emerald-200': extData.status === 'registered', 'border-amber-100 hover:border-amber-200': extData.status === 'in_call', 'border-rose-100 hover:border-rose-200': extData.status === 'offline' }" >
                        <!-- Línea superior de estado -->
                        <div
                            class="absolute top-0 left-0 right-0 h-1"
                            :class="{
                                'bg-emerald-400': extData.status === 'registered',
                                'bg-amber-400': extData.status === 'in_call',
                                'bg-rose-300': extData.status === 'offline'
                            }">
                        </div>
                        <div class="flex items-center justify-between">
                            <!-- Información -->
                            <div class="flex items-center gap-4">
                                <!-- Icono grande -->
                                <div
                                    class="relative w-16 h-16 rounded-2xl flex items-center justify-center
                                           border transition-all duration-300"
                                    :class="{
                                        'bg-emerald-50 text-emerald-600 border-emerald-100 group-hover:bg-emerald-100': extData.status === 'registered',
                                        'bg-amber-50 text-amber-600 border-amber-100 group-hover:bg-amber-100': extData.status === 'in_call',
                                        'bg-rose-50 text-rose-500 border-rose-100 group-hover:bg-rose-100': extData.status === 'offline'
                                    }"
                                >
                                    <!-- Halo -->
                                    <div
                                        class="absolute inset-1 rounded-xl opacity-40"
                                        :class="{
                                            'bg-emerald-100': extData.status === 'registered',
                                            'bg-amber-100': extData.status === 'in_call',
                                            'bg-rose-100': extData.status === 'offline'
                                        }">
                                    </div>
                                    <!-- Icono -->
                                    <i
                                        class="fa-solid relative z-10 text-3xl drop-shadow-sm"
                                        :class="{
                                            'fa-phone': extData.status === 'registered',
                                            'fa-phone-volume animate-bounce': extData.status === 'in_call',
                                            'fa-phone-slash': extData.status === 'offline'
                                        }">
                                    </i>
                                </div>
                                <!-- Texto -->
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase
                                                 tracking-[0.15em] font-semibold block">
                                        Extensión
                                    </span>
                                    <span
                                        class="text-2xl font-bold text-slate-900 tracking-tight block mt-0.5"
                                        x-text="extNum">
                                    </span>
                                     <!-- Dentro del loop de extensiones en tu HTML -->
                                    <div class="flex items-center gap-2 mt-1">
                                        <!-- Punto -->
                                        <span class="w-2 h-2 rounded-full"
                                              :class="{
                                                  'bg-emerald-400 animate-pulse': extData.status === 'registered',
                                                  'bg-amber-500 animate-ping': extData.status === 'in_call',
                                                  'bg-rose-400': extData.status === 'offline'
                                              }">
                                        </span>
                                    
                                        <!-- Texto de Estado o Duración si está en llamada -->
                                        <!-- Si está 'registered' o 'offline' muestra esto -->
                                        <span class="text-[10px] uppercase tracking-wider font-semibold"
                                              :class="{
                                                  'text-emerald-600': extData.status === 'registered',
                                                  'text-rose-500': extData.status === 'offline'
                                              }"
                                              x-show="extData.status !== 'in_call'"
                                              x-text="extData.status === 'registered' ? 'En línea' : 'Desconectado'">
                                        </span>
                                    
                                        <!-- Si está 'in_call' muestra esto (aquí puedes personalizar el tamaño del segundero) -->
                                        <div x-show="extData.status === 'in_call'" class="flex items-center gap-1.2 text-amber-600">
                                            <span class="text-[10px] uppercase tracking-wider font-semibold"></span>
                                            <!-- Aquí modificas el tamaño (ej: text-xs, text-sm, font-bold) solo para el reloj -->
                                            <span class="text-xl font-bold font-mono" x-text="formatTime(timers[extNum])"></span>
                                        </div>
                                    </div>
        
                                </div>
        
                            </div>
        
        
                            <!-- Estado Badge Derecho -->
                            <div class="flex flex-col items-end">
        
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center border"
                                    :class="{
                                        'bg-emerald-50 border-emerald-100 text-emerald-500': extData.status === 'registered',
                                        'bg-amber-50 border-amber-100 text-amber-600': extData.status === 'in_call',
                                        'bg-rose-50 border-rose-100 text-rose-400': extData.status === 'offline'
                                    }"
                                >
                                    <i
                                        class="fa-solid text-sm"
                                        :class="{
                                            'fa-check': extData.status === 'registered',
                                            'fa-phone': extData.status === 'in_call',
                                            'fa-xmark': extData.status === 'offline'
                                        }">
                                    </i>
                                </div>
        
                            </div>
        
                        </div>
        
        
                        <!-- Barra inferior MODIFICADA con los botones -->
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                            
                            <div>
                                <span class="text-[10px] uppercase tracking-wider text-slate-400 block mb-0.5">
                                    Estado SIP
                                </span>
                                <span
                                    class="text-[10px] font-semibold uppercase tracking-wider"
                                    :class="{
                                        'text-emerald-600': extData.status === 'registered',
                                        'text-amber-600': extData.status === 'in_call',
                                        'text-rose-500': extData.status === 'offline'
                                    }"
                                    x-text="{
                                        'registered': 'Registrado',
                                        'in_call': 'En llamada',
                                        'offline': 'Sin registro'
                                    }[extData.status]">
                                </span>
                            </div>

                            <!-- Nuevos Botones de Espiar y Colgar (Solo se muestran cuando está en llamada) -->
                            <div class="flex items-center gap-2" x-show="extData.status === 'in_call'" x-cloak>
                                <button @click="hangupCall(extNum)" class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-colors border border-rose-100 shadow-sm" title="Colgar Llamada">
                                    <i class="fa-solid fa-phone-slash text-xs"></i>
                                </button>
                            </div>
        
                        </div>
        
                    </div>
        
                </template>
        
        
                <!-- Estado Vacío -->
                <template x-if="Object.keys(extensions).length === 0">
        
                    <div
                        class="col-span-full py-16 text-center text-slate-500
                               flex flex-col items-center justify-center gap-4"
                    >
        
                        <div
                            class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-200
                                   flex items-center justify-center"
                        >
                            <i class="fa-solid fa-phone-slash text-slate-400 text-2xl"></i>
                        </div>
        
                        <div>
                            <p class="text-sm font-semibold text-slate-600">
                                No hay extensiones registradas
                            </p>
        
                            <p class="text-xs text-slate-400 mt-1">
                                No se detectaron dispositivos SIP activos en este momento.
                            </p>
                        </div>
        
                    </div>
        
                </template>
        
            </div>
        </div>
    </main>
</div>

<!-- Script Alpine.js -->
<script>
function companyPbxData() {
    return {
        extensions: {},
        timers: {},
        activeDidCall: null,   // Datos de la llamada al DID (ej: { caller_id: '3001234567', did: '123456' })
        activeDidTimer: 0,     // Segundero de la llamada al DID
        
        initDashboard() {
            this.fetchStatus();

            // 1. Consulta al backend cada 5 segundos
            setInterval(() => {
                this.fetchStatus();
            }, 5000);

            // 2. Reloj local de 1 segundo para extensiones y DID
            setInterval(() => {
                // Reloj de extensiones
                for (let extNum in this.extensions) {
                    if (this.extensions[extNum].status === 'in_call') {
                        if (!this.timers[extNum]) this.timers[extNum] = 0;
                        this.timers[extNum]++;
                    } else {
                        this.timers[extNum] = 0;
                    }
                }

                // Reloj del DID principal
                if (this.activeDidCall) {
                    this.activeDidTimer++;
                } else {
                    this.activeDidTimer = 0;
                }
            }, 1000);
        },

        fetchStatus() {
            fetch(`{{ route('admin.pbx.realtime-status') }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        this.extensions = data.extensions;
                        // El backend debe retornar esto si hay llamada activa en el DID
                        this.activeDidCall = data.active_did_call || null; 
                    }
                })
                .catch(error => console.error('Error al sincronizar estado PBX:', error));
        },

        formatTime(totalSeconds) {
            if (!totalSeconds) return '00:00';
            let minutes = Math.floor(totalSeconds / 60);
            let seconds = totalSeconds % 60;
            return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        },

        // --- FUNCIONES MEJORADAS PARA ESPIAR Y COLGAR CON SWEETALERT Y AUDIO STREAM ---

        hangupCall(extNum) {
            Swal.fire({
                title: '¿Colgar llamada?',
                text: "Se desconectará la llamada actual de la extensión " + extNum + " de forma forzada.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fa-solid fa-phone-slash mr-2"></i> Sí, colgar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ route('admin.pbx.control-action') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            action: 'hangup',
                            extension: extNum
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success' || data.success) {

                            Swal.fire('¡Colgada!', 'La llamada ha sido finalizada correctamente.', 'success');
                            this.fetchStatus();
                        } else {
                            Swal.fire('Atención', 'No se pudo colgar la llamada: ' + (data.message || 'Error desconocido'), 'warning');
                        }
                    }).catch(err => {
                        Swal.fire('Error', 'Hubo un problema de conexión con el servidor.', 'error');
                        console.error(err);
                    });
                }
            });
        }
    }
}
</script>
@endsection