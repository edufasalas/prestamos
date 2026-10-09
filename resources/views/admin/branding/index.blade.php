@extends('layouts.app')

@section('title', 'Configuración de Branding')

@section('content')
<!-- Topbar Global fuera del container con padding para que ocupe todo el ancho -->
@php
    $currentUser = auth()->user();
    $userExt = $currentUser ? \App\Models\UserExtension::where('user_id', $currentUser->id)->first() : null;
    $sipExtension = $userExt ? $userExt->extension_number : 'N/A';
@endphp

@include('layouts.topbar', [
    'showSoftphone' => true, 
    'title' => 'Configuración de Branding',
    'sipExtension' => $sipExtension
])
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- Encabezado de la Sección -->
    <div class="mb-8">
        <p class="text-sm text-slate-500 mt-1">Personaliza la identidad visual de la plataforma (Nombre, Logo y Color Principal).</p>
    </div>

    <!-- Alerta de éxito si existe -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Formulario Principal -->
    <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden divide-y divide-slate-100">
        @csrf
        @method('PUT')

        <!-- Sección: Nombre de la Empresa -->
        <div class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Nombre de la Empresa</h2>
                    <p class="text-xs text-slate-500">Aparecerá en los encabezados y reportes.</p>
                </div>
            </div>

            <div class="max-w-xl">
                <input type="text" 
                       name="name" 
                       value="{{ old('name', $globalCompany->name ?? '') }}" 
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/15 transition-all"
                       placeholder="Ej. Arkas Comunicaciones">
                @error('name')
                    <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Sección: Logo Corporativo -->
        <div class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-image text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Logotipo</h2>
                    <p class="text-xs text-slate-500">Sube una imagen en formato PNG o SVG.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <!-- Vista previa actual -->
                <div class="w-20 h-20 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-center p-3 shrink-0 shadow-inner">
                    @if(!empty($globalCompany->logo_path) && file_exists(public_path($globalCompany->logo_path)))
                        <img src="{{ url('public/' . $globalCompany->logo_path) }}" alt="Logo actual" class="w-full h-full object-contain">
                    @else
                        <i class="fa-solid fa-headset text-slate-400 text-xl"></i>
                    @endif
                </div>

                <!-- Input File -->
                <div class="w-full max-w-xl">
                    <input type="file" 
                           name="logo" 
                           accept="image/png, image/jpeg, image/svg+xml"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer transition-all border border-slate-200 rounded-xl">
                    <p class="text-[10px] text-slate-400 mt-1.5">Formatos permitidos: PNG, JPG, SVG (Máx. 2MB).</p>
                    @error('logo')
                        <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Sección: Color Principal -->
        <div class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-palette text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Color Principal de Marca</h2>
                    <p class="text-xs text-slate-500">Define el tono corporativo para botones y avatares.</p>
                </div>
            </div>

            <div class="flex items-center gap-4 max-w-xl">
                <input type="color" 
                       name="primary_color" 
                       value="{{ old('primary_color', $globalCompany->primary_color ?? '#2052F7') }}"
                       class="w-12 h-11 rounded-xl border border-slate-200 p-1 cursor-pointer bg-white">
                
                <input type="text" 
                       name="primary_color_text" 
                       value="{{ old('primary_color', $globalCompany->primary_color ?? '#2052F7') }}" 
                       disabled
                       class="w-32 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 bg-slate-50">
                
                <span class="text-xs text-slate-400">Código Hexadecimal</span>
            </div>
            @error('primary_color')
                <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Botones de Acción -->
        <div class="p-6 bg-slate-50/50 flex items-center justify-end gap-3">
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl text-xs font-black text-white shadow-lg shadow-blue-600/20 hover:scale-[1.02] active:scale-95 transition-all cursor-pointer"
                    style="background-color: {{ $globalCompany->primary_color ?? '#2052F7' }};">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection