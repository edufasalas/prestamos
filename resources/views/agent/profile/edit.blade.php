@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full">
    
    <!-- Topbar Corporativo con el título correspondiente -->
    @include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Mi Perfil de Usuario'])

    <!-- Contenido Principal con Scroll Interno -->
    <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- Encabezado de Sección -->
            <div class="flex items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Configuración de la Cuenta</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Actualiza tu información personal y credenciales de acceso al sistema.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs flex items-center shadow-xs">
                    <i class="fa-solid fa-circle-check mr-2 text-emerald-500 text-sm"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario de Actualización -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 md:p-8">
                <form action="{{ route('agent.profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nombre Completo</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-5 mt-5">
                        <h3 class="text-xs font-extrabold text-indigo-600 uppercase tracking-wider mb-4">Cambiar Contraseña</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nueva Contraseña</label>
                                <input type="password" name="password" placeholder="••••••••" 
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">Déjalo en blanco si no deseas cambiarla.</span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Confirmar Nueva Contraseña</label>
                                <input type="password" name="password_confirmation" placeholder="••••••••" 
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded-xl transition-colors shadow-sm cursor-pointer flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection