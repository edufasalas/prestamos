@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50 relative w-full"
     x-data="{ 
        openModal: {{ $errors->any() && !old('company_id') ? 'true' : 'false' }}, 
        editMode: false,
        companyId: '',
        companyName: '',
        companyStatus: 'active',
        minutesLimit: 1000,
        incomingDid: '',
        outboundCallerid: '',
        maxConcurrentCalls: 5,
        hasIvr: false,
        adminName: '',
        adminEmail: '',
        openCreateModal() {
            this.editMode = false;
            this.companyId = '';
            this.companyName = '';
            this.companyStatus = 'active';
            this.minutesLimit = 1000;
            this.incomingDid = '';
            this.outboundCallerid = '';
            this.maxConcurrentCalls = 5;
            this.hasIvr = false;
            this.adminName = '';
            this.adminEmail = '';
            this.openModal = true;
        },
        openEditModal(company) {
            this.editMode = true;
            this.companyId = company.id;
            this.companyName = company.name;
            this.companyStatus = company.status;
            this.minutesLimit = company.minutes_limit ?? 1000;
            this.incomingDid = company.incoming_did || '';
            this.outboundCallerid = company.outbound_callerid || '';
            this.maxConcurrentCalls = company.max_concurrent_calls ?? 5;
            this.hasIvr = company.has_ivr ? true : false;
            
            // Extraemos el primer usuario administrador si existe
            let admin = (company.users && company.users.length > 0) ? company.users[0] : {};
            this.adminName = admin.name || '';
            this.adminEmail = admin.email || '';
            
            this.openModal = true;
        }
     }">
    
    <!-- Topbar Corporativo -->
    @php
        $currentUser = auth()->user();
        $userExt = $currentUser ? \App\Models\UserExtension::where('user_id', $currentUser->id)->first() : null;
        $sipExtension = $userExt ? $userExt->extension_number : 'N/A';
    @endphp

    @include('layouts.topbar', [
        'showSoftphone' => true, 
        'title' => 'Gestión de Tenants',
        'sipExtension' => $sipExtension
    ])

    <!-- Contenido Principal con Scroll Interno -->
    <div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado de Sección y Botón Nueva Empresa -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Gestión de Tenants (Clientes)</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Da de alta nuevas empresas, edita su información o suspende sus accesos.</p>
                </div>
                <button @click="openCreateModal()" class="w-full sm:w-auto px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm cursor-pointer flex items-center justify-center gap-2 active:scale-95">
                    <i class="fa-solid fa-building-user text-sm"></i> Nueva Empresa
                </button>
            </div>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center shadow-xs">
                    <i class="fa-solid fa-circle-check mr-2 text-emerald-500 text-base"></i> {{ session('success') }}
                </div>
            @endif

            <!-- Tarjeta Contenedora de la Tabla / Responsiva -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-4 sm:px-6">ID</th>
                                <th class="py-3.5 px-4 sm:px-6">Empresa / DID Principal</th>
                                <th class="py-3.5 px-4 sm:px-6">Estado</th>
                                <th class="py-3.5 px-4 sm:px-6">Bolsa Minutos</th>
                                <th class="py-3.5 px-4 sm:px-6">Canales / IVR</th>
                                <th class="py-3.5 px-4 sm:px-6">Usuarios</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                            @forelse ($companies as $company)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-900">#{{ $company->id }}</td>
                                    <td class="py-3.5 px-4 sm:px-6">
                                        <div class="font-bold text-slate-900">{{ $company->name }}</div>
                                        <div class="text-[11px] text-indigo-600 font-medium">DID: {{ $company->incoming_did ?? 'No asignado' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-6">
                                        @if($company->status == 'active')
                                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase">Activa</span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-md bg-rose-50 text-rose-600 border border-rose-100 uppercase">Suspendida</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-6">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100">
                                            {{ $company->minutes_limit ?? 1000 }} min
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-6 text-slate-600">
                                        <div class="text-xs font-semibold">{{ $company->max_concurrent_calls ?? 5 }} canales</div>
                                        <div class="text-[10px] text-slate-400">IVR: {{ $company->has_ivr ? 'Sí' : 'No' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-600">
                                        {{ $company->users_count ?? 0 }} usr
                                    </td>
                                    <td class="py-3.5 px-4 sm:px-6 text-right">
                                        <button @click='openEditModal(@json($company))' class="p-2 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-xl transition-all cursor-pointer shadow-xs active:scale-95" title="Editar Empresa">
                                            <i class="fa-solid fa-sliders text-xs"></i>
                                        </button>
                                        <form id="delete-form-{{ $company->id }}" action="{{ route('superadmin.companies.destroy', $company->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('{{ $company->id }}', '{{ $company->name }}')" class="p-2 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-xl transition-all cursor-pointer shadow-xs active:scale-95" title="Eliminar Empresa">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>                                        
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">No hay empresas registradas en la plataforma.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL UNIFICADO (CREAR Y EDITAR EMPRESA) -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4 overflow-y-auto" style="display: none;" x-cloak>
        <div @click.outside="openModal = false" class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 sm:p-8 border border-slate-200 max-h-[90vh] overflow-y-auto space-y-6">
            
            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid" :class="editMode ? 'fa-building-gear text-indigo-600' : 'fa-building-user text-indigo-600'"></i>
                    <span x-text="editMode ? 'Editar Parámetros de la Empresa' : 'Registrar Nueva Empresa (Tenant)'"></span>
                </h3>
                <button @click="openModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs space-y-1">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form :action="editMode ? '{{ url('superadmin/companies') }}/' + companyId : '{{ route('superadmin.companies.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nombre de la Empresa</label>
                        <input type="text" name="company_name" x-model="companyName" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all" placeholder="Ej: Telecomunicaciones S.A.">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Estado de la Empresa</label>
                        <select name="status" x-model="companyStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="active">Activa</option>
                            <option value="suspended">Suspendida</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN: PARÁMETROS DE TELEFONÍA / PBX -->
                <div class="p-4 bg-indigo-50/40 rounded-2xl border border-indigo-100/80 space-y-3">
                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-phone-volume"></i> Configuración de Telefonía (PBX / DID)
                    </h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Número Entrante (DID)</label>
                            <input type="text" name="incoming_did" x-model="incomingDid" 
                                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600" placeholder="Ej: 5716001234">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">CallerID Saliente</label>
                            <input type="text" name="outbound_callerid" x-model="outboundCallerid" 
                                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600" placeholder="Ej: 5716001234">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Canales Simultáneos</label>
                            <input type="number" name="max_concurrent_calls" x-model="maxConcurrentCalls" min="1" max="100" 
                                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600" placeholder="5">
                        </div>

                        <div class="flex items-center pt-5">
                            <label class="relative flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="has_ivr" value="1" x-model="hasIvr" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-700">¿Tiene IVR Principal Activo?</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Recargar / Ajustar Minutos (+ o -)</label>
                    <input type="number" name="minutes_to_add" value="0" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all" placeholder="Ej: 10 para sumar, o -10 para restar">
                    <span class="text-[10px] text-slate-400 mt-1 block">Escribe cuántos minutos deseas sumarle a su bolsa actual (deja en 0 si no deseas modificar sus minutos).</span>
                </div>

                <!-- SECCIÓN DEL ADMINISTRADOR -->
                <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-3">
                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-shield"></i> 
                        <span x-text="editMode ? 'Editar Administrador de la Empresa' : 'Primer Usuario Administrador'"></span>
                    </h4>
                     
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nombre del Administrador</label>
                        <input type="text" name="admin_name" x-model="adminName" required 
                            class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600" placeholder="Ej: Juan Pérez">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Correo Electrónico (Login)</label>
                        <input type="email" name="admin_email" x-model="adminEmail" required 
                            class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600" placeholder="admin@empresa.com">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                            Contraseña 
                            <span class="text-slate-400 font-normal" x-show="editMode">(Déjala en blanco si no deseas cambiarla)</span>
                        </label>
                        <input type="password" name="admin_password" autocomplete="new-password" :required="!editMode" 
                            class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm cursor-pointer active:scale-95" x-text="editMode ? 'Actualizar Cambios' : 'Guardar Empresa'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function confirmDelete(companyId, companyName) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Vas a eliminar la empresa "${companyName}" y todos sus usuarios asociados. ¡Esta acción no se puede deshacer!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-slate-100',
                confirmButton: 'px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider',
                cancelButton: 'px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + companyId).submit();
            }
        });
    }
</script>
@endsection