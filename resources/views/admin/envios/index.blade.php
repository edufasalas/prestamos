@extends('layouts.app')
@section('content')
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Envio de correos'])
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<div class="min-h-screen bg-slate-100 p-6" x-data="{ openSmtpModal: false, openTemplateModal: false }">
    <div class="max-w-7xl mx-auto">
        
        {{-- Cabecera --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Módulo de Envíos y Plantillas</h1>
                <p class="text-sm text-slate-500">Gestiona tus comunicaciones y configuraciones SMTP por empresa.</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Botón para abrir el Modal SMTP --}}
                <button @click="openSmtpModal = true" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Configurar SMTP
                </button>
                {{-- Botón para abrir el Modal de Plantilla --}}
                <button @click="openTemplateModal = true" type="button" class="inline-flex items-center px-4 py-2 bg-slate-900 border border-transparent rounded-lg text-sm font-medium text-white shadow-sm hover:bg-slate-800 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Plantilla
                </button>
            </div>
        </div>

        {{-- Alertas de Sesión --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded-r-lg shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Formulario Principal de Envío --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Enviar Correo a Cliente
                        </h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.envios.enviar') }}" method="POST" class="space-y-5">
                            @csrf
                            
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium text-slate-700 mb-1">Seleccionar Cliente:</label>
                                <select name="cliente_id" id="cliente_id" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Seleccione un cliente --</option>
                                    @foreach($clientes as $cli)
                                        <option value="{{ $cli->id }}" {{ isset($clienteSeleccionado) && $clienteSeleccionado->id == $cli->id ? 'selected' : '' }}>
                                            {{ $cli->nombre }} ({{ $cli->correo ?? 'Sin correo' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

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

                            <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition shadow-sm">
                                Enviar Correo Ahora
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Panel Lateral: Listado de Plantillas --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Plantillas Disponibles
                        </h2>
                    </div>
                    <div class="divide-y divide-slate-100 max-h-[420px] overflow-y-auto">
                        @forelse($templates as $tmp)
                            <div class="p-4 hover:bg-slate-50 transition">
                                <h3 class="font-semibold text-sm text-slate-800 mb-1">{{ $tmp->titulo }}</h3>
                                <p class="text-xs text-slate-500 mb-1"><strong class="text-slate-600">Asunto:</strong> {{ $tmp->asunto }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $tmp->cuerpo }}</p>
                            </div>
                        @empty
                            <div class="p-6 text-center text-sm text-slate-400">
                                No hay plantillas creadas todavía.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL 1: Configurar SMTP (Alpine.js) --}}
    <div x-cloak x-show="openSmtpModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openSmtpModal = false" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full overflow-hidden border border-slate-100">
            <form action="{{ route('admin.smtp.update') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Configuración SMTP de la Empresa</h3>
                    <button @click="openSmtpModal = false" type="button" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Host SMTP:</label>
                            <input type="text" name="mail_host" value="{{ $smtp->mail_host ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Puerto:</label>
                            <input type="number" name="mail_port" value="{{ $smtp->mail_port ?? '587' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Usuario SMTP:</label>
                            <input type="text" name="mail_username" value="{{ $smtp->mail_username ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña SMTP:</label>
                            <input type="password" name="mail_password" value="{{ $smtp->mail_password ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Encriptación:</label>
                            <input type="text" name="mail_encryption" value="{{ $smtp->mail_encryption ?? 'tls' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Correo Remitente:</label>
                            <input type="email" name="mail_from_address" value="{{ $smtp->mail_from_address ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Remitente:</label>
                            <input type="text" name="mail_from_name" value="{{ $smtp->mail_from_name ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                    {{-- Botón independiente para probar correo --}}
                    <button type="submit" formaction="{{ route('admin.smtp.probar') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 shadow-sm transition">
                        Enviar Correo de Prueba
                    </button>
                    
                    <div class="flex gap-2">
                        <button @click="openSmtpModal = false" type="button" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm">Guardar SMTP</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Crear Nueva Plantilla (Alpine.js) --}}
    <div x-cloak x-show="openTemplateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="openTemplateModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-100">
            <form action="{{ route('admin.envios.storeTemplate') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Crear Nueva Plantilla</h3>
                    <button @click="openTemplateModal = false" type="button" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Título interno:</label>
                        <input type="text" name="titulo" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Bienvenida Clientes" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Asunto del Correo:</label>
                        <input type="text" name="asunto" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Hola {nombre}, bienvenido" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Cuerpo del Mensaje:</label>
                        <textarea name="cuerpo" rows="5" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Escribe tu mensaje usando {nombre} y {telefono}..." required></textarea>
                    </div>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="openTemplateModal = false" type="button" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm">Guardar Plantilla</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection