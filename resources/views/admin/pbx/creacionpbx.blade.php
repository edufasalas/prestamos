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
        'title' => 'Creación y Enrutamiento IVR Multi-Opción - ' . $company->name,
        'sipExtension' => $sipExtension
    ])

    <!-- Contenedor Principal Institucional -->
    <main class="flex-1 p-6 lg:p-8 max-w-7xl mx-auto w-full space-y-6">
        
        <!-- CABECERA DE EMPRESA -->
        <div class="bg-gradient-to-r from-white via-white to-indigo-50/30 p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl text-white flex items-center justify-center text-3xl shrink-0 shadow-md shadow-indigo-200 border border-indigo-400/30" style="background-color: {{ $company->primary_color ?? '#2052F7' }}">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-xl lg:text-2xl font-normal text-slate-900 tracking-tight">
                            {{ $company->name }} - Configuración de IVR & DID
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-normal rounded-full uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            Asterisk PBX Manager
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Configure la línea troncal, cargue el audio interactivo y asigne las opciones del menú de marcación.
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pbx.companypbx') }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-medium transition-all shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Volver al Monitoreo</span>
                </a>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- PANEL DE PROVISIONAMIENTO PROFESIONAL -->
        <!-- ========================================= -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-8">
            <div class="flex items-center justify-between pb-5 border-b border-slate-200">
                <div>
                    <h2 class="text-base lg:text-lg text-slate-900 font-semibold flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                            <i class="fa-solid fa-sitemap text-lg"></i>
                        </div>
                        <div>
                            <span>Asistente de Menú Interactivo (IVR)</span>
                            <p class="text-xs text-slate-500 font-normal mt-0.5">Definición de flujos de llamada, audios y opciones numéricas</p>
                        </div>
                    </h2>
                </div>
            </div>

            <form @submit.prevent="submitIvrDidConfig" class="space-y-8" enctype="multipart/form-data">
                
                <!-- PASO 1: NÚMERO DID ENTRANTE -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">
                        1. Número DID Entrante (Línea Troncal)
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-globe"></i>
                            </span>
                            <input type="text" 
                                   x-model="form.did" 
                                   placeholder="Ej: +5715088888" 
                                   class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500/20 font-mono text-sm outline-none transition-all">
                        </div>
                        <div class="flex items-center text-xs text-slate-500 bg-indigo-50/40 p-3.5 rounded-xl border border-indigo-100/50">
                            <i class="fa-solid fa-circle-info text-indigo-600 mr-2.5 text-sm shrink-0"></i>
                            <span>El formato debe incluir el código internacional y prefijo de área sin espacios.</span>
                        </div>
                    </div>
                </div>

                <!-- PASO 2: NOMBRE DEL PERFIL / IVR -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">
                        2. Nombre del IVR en la Centralita
                    </label>
                    <input type="text" 
                           x-model="form.ivr_name" 
                           placeholder="Ej: IVR_Principal_Empresa" 
                           class="w-full max-w-md px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-600 text-sm outline-none font-mono">
                </div>

                <!-- PASO 3: SUBIR LOCUCIÓN DE AUDIO (WAV / MP3) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">
                        3. Carga del Audio del IVR (Locución que dice: "Marque 1 para Ventas...")
                    </label>
                    <div class="border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-2xl p-6 transition-all bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4 w-full">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-file-audio"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-xs font-semibold text-slate-900 block truncate" x-text="form.recording_file ? form.recording_file.name : 'Ningún archivo seleccionado'"></span>
                                <span class="text-[11px] text-slate-500 block">Formato recomendado: WAV (PCM 8kHz o 16kHz mono) o MP3 de alta calidad.</span>
                            </div>
                        </div>
                        <label class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer shadow-xs shrink-0 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-folder-open text-indigo-600"></i>
                            <span>Examinar audio</span>
                            <input type="file" @change="form.recording_file = $event.target.files[0]" accept="audio/*" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- PASO 4: OPCIONES DE MARCACIÓN DEL IVR (1, 2, 3...) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            4. Mapeo de Opciones del Menú (DTMF)
                        </label>
                        <button type="button" @click="addIvrOption()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 border border-indigo-200">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Agregar Opción</span>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(option, index) in form.ivr_options" :key="index">
                            <div class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 bg-slate-50/60">
                                <!-- Dígito -->
                                <div class="w-28 shrink-0">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Dígito (Ej: 1)</label>
                                    <input type="text" 
                                           x-model="option.digit" 
                                           maxlength="2" 
                                           placeholder="1" 
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white font-mono text-center text-sm font-bold focus:border-indigo-600 outline-none">
                                </div>

                                <!-- Descripción / Destino etiqueta -->
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Descripción (Ej: Ventas)</label>
                                    <input type="text" 
                                           x-model="option.description" 
                                           placeholder="Ej: Ventas / Comercial" 
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm focus:border-indigo-600 outline-none">
                                </div>

                                <!-- Extensión Destino -->
                                <div class="w-40 shrink-0">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Extensión Destino</label>
                                    <select x-model="option.extension" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm font-mono focus:border-indigo-600 outline-none">
                                        <option value="">Seleccione...</option>
                                        <template x-for="(extData, extNum) in extensions" :key="extNum">
                                            <option :value="extNum" x-text="extNum + ' (' + (extData.caller_id_name || 'Extension') + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Botón eliminar -->
                                <div class="pt-5 shrink-0">
                                    <button type="button" @click="removeIvrOption(index)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Eliminar opción">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- BOTÓN DE ACCIÓN PRINCIPAL -->
                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-4">
                    <button type="submit" 
                            class="px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm transition-all shadow-md shadow-indigo-200 flex items-center gap-2.5 cursor-pointer">
                        <i class="fa-solid fa-circle-check text-base"></i>
                        <span>Compilar IVR, Subir Audio y Sincronizar Asterisk</span>
                    </button>
                </div>

            </form>
        </div>

    </main>
</div>

<!-- Script Alpine.js de gestión avanzada -->
<script>
function companyPbxData() {
    return {
        extensions: {},
        form: {
            did: '{{ $company->incoming_did ?? "" }}',
            ivr_name: 'IVR_Principal',
            recording_file: null,
            ivr_options: [
                { digit: '1', description: 'Ventas', extension: '101' },
                { digit: '2', description: 'Soporte', extension: '102' }
            ]
        },

        initDashboard() {
            this.fetchStatus();
            setInterval(() => { this.fetchStatus(); }, 5000);
        },

        fetchStatus() {
            fetch(`{{ route('admin.pbx.realtime-status') }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        this.extensions = data.extensions;
                    }
                })
                .catch(error => console.error('Error al sincronizar estado PBX:', error));
        },

        addIvrOption() {
            let nextDigit = (this.form.ivr_options.length + 1).toString();
            this.form.ivr_options.push({ digit: nextDigit, description: '', extension: '' });
        },

        removeIvrOption(index) {
            this.form.ivr_options.splice(index, 1);
        },

        submitIvrDidConfig() {
            let formData = new FormData();
            formData.append('did', this.form.did);
            formData.append('ivr_name', this.form.ivr_name);
            if (this.form.recording_file) {
                formData.append('recording_file', this.form.recording_file);
            }
            // Adjuntamos las opciones del IVR codificadas en JSON para que el backend/API las procese
            formData.append('ivr_options', JSON.stringify(this.form.ivr_options));

            Swal.fire({
                title: '¿Provisionar IVR en Asterisk?',
                text: "Se registrará el DID, se subirá la locución y se configurarán las opciones de marcación.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, sincronizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ route('admin.pbx.store-ivrdid') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.ok) {
                            Swal.fire('¡Provisionado!', data.msg || 'El menú IVR y el DID se configuraron correctamente.', 'success');
                        } else {
                            Swal.fire('Atención', data.msg || 'No se pudo procesar la solicitud.', 'warning');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Error de conexión con el servidor PBX.', 'error');
                        console.error(err);
                    });
                }
            });
        }
    }
}
</script>
@endsection