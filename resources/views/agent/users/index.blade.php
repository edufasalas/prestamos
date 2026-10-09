@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full"
     x-data="{ 
        openModal: {{ $errors->any() && !old('user_id') ? 'true' : 'false' }}, 
        editMode: false,
        userId: '',
        name: '',
        email: '',
        role: 'agent',
        status: 'active',
        extension_number: '',
        sip_secret: '',
        sip_proxy: '',
        openCreateModal() {
            this.editMode = false;
            this.userId = '';
            this.name = '';
            this.email = '';
            this.role = 'agent';
            this.status = 'active';
            this.extension_number = '';
            this.sip_secret = '';
            this.sip_proxy = '';
            this.openModal = true;
        },
        openEditModal(user) {
            this.editMode = true;
            this.userId = user.id;
            this.name = user.name;
            this.email = user.email;
            this.role = user.role;
            this.status = user.status;
            this.extension_number = user.active_extension ? user.active_extension.extension_number : '';
            this.sip_secret = ''; 
            this.sip_proxy = user.active_extension ? user.active_extension.websocket_url : '';
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
    'title' => 'Gestión de Usuarios',
    'sipExtension' => $sipExtension
])

    <!-- Contenido Principal con Scroll Interno -->
    <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Encabezado de Sección y Botón Nuevo -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-xs text-slate-500 mt-0.5">Administra los accesos al sistema y las extensiones telefónicas de los agentes.</p>
                    </div>
                </div>
                <button @click="openCreateModal()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded-xl transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
                </button>
            </div>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs flex items-center shadow-xs">
                    <i class="fa-solid fa-circle-check mr-2 text-emerald-500 text-sm"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs flex items-center shadow-xs">
                    <i class="fa-solid fa-circle-xmark mr-2 text-rose-500 text-sm"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Tarjeta Contenedora de la Tabla -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                
                <div class="overflow-x-auto w-full">
                    <table id="users-table" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3 px-4">Nombre / Email</th>
                                <th class="py-3 px-4">Extensión SIP</th>
                                <th class="py-3 px-4">Proxy / Servidor</th>
                                <th class="py-3 px-4">Rol</th>
                                <th class="py-3 px-4">Estado DB</th>
                                <th class="py-3 px-4">Conexión</th>
                                <th class="py-3 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @foreach ($users as $user)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $user->id }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                        {{ $user->activeExtension->extension_number ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs text-slate-500">
                                        {{ $user->activeExtension->name ?? 'Proxy por defecto' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-slate-100 text-slate-700 uppercase tracking-wide">{{ $user->role }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($user->status == 'active')
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase">Activo</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-rose-50 text-rose-600 border border-rose-100 uppercase">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if(method_exists($user, 'isOnline') && $user->isOnline(5))
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                                En línea
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-slate-400 rounded-full"></span>
                                                Desconectado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right space-x-1">
                                        <!-- Botón Editar -->
                                        <button @click='openEditModal(@json($user))' class="p-2 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-lg transition-colors cursor-pointer inline-flex items-center" title="Configurar cuenta SIP y Usuario">
                                            <i class="fa-solid fa-sliders text-xs"></i>
                                        </button>

                                        <!-- Botón Eliminar con SweetAlert -->
                                        <form action="{{ route('agent.users.destroy', $user->id) }}" method="POST" class="inline-block delete-user-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this)" class="p-2 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-lg transition-colors cursor-pointer inline-flex items-center" title="Eliminar usuario">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL UNIFICADO (CREAR Y EDITAR) -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4" style="display: none;">
        <div @click.outside="openModal = false" class="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-slate-200 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fa-solid" :class="editMode ? 'fa-user-gear text-indigo-600' : 'fa-user-plus text-indigo-600'"></i>
                    <span class="ml-2" x-text="editMode ? 'Editar Usuario y Configuración SIP' : 'Crear Nuevo Usuario'"></span>
                </h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form :action="editMode ? '{{ url('agent/users') }}/' + userId : '{{ route('agent.users.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre Completo</label>
                        <input type="text" name="name" x-model="name" required 
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo Electrónico</label>
                        <input type="email" name="email" x-model="email" required 
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Contraseña <span class="text-slate-400 font-normal" x-show="editMode">(Dejar en blanco para mantener actual)</span>
                    </label>
                    <input type="password" name="password" :required="!editMode" placeholder="••••••••"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- SECCIÓN DE INFRAESTRUCTURA TELEFÓNICA / SIP -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/70 space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 flex items-center">
                        <i class="fa-solid fa-headset mr-1.5"></i> Configuración Troncal SIP
                    </h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Extensión SIP</label>
                            <input type="text" name="extension_number" x-model="extension_number" placeholder="Ej: 1001" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Secreto SIP</label>
                            <input type="password" name="sip_secret" x-model="sip_secret" placeholder="••••••••" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Servidor / Proxy SIP</label>
                        <select name="websocket_url" x-model="sip_proxy" required
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccione un servidor SIP...</option>
                            @foreach($sipServers as $server)
                                <option value="{{ $server->websocket_url }}">
                                    {{ $server->name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rol</label>
                        <select name="role" x-model="role" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="agent">Agente</option>
                            <option value="supervisor">Supervisor</option>
                            
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Estado</label>
                        <select name="status" x-model="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase rounded-lg transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded-lg transition-colors shadow-sm cursor-pointer" x-text="editMode ? 'Actualizar Cambios' : 'Guardar Usuario'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<!-- Asegúrate de tener incluido SweetAlert2 en tu layout principal (app.blade.php) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#users-table').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'excel', 'pdf', 'copy', 'print'
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });
    });

    function confirmDelete(button) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará al usuario y su extensión SIP vinculada permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#cbd5e1',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                button.closest('form').submit();
            }
        });
    }
</script>
@endpush
@endsection