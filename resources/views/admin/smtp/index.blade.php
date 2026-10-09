@extends('layouts.app')

@section('content')
@include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Configuración SMTP'])
<div class="min-h-screen bg-slate-100 p-6">
    <div class="max-w-4xl mx-auto">
        
        {{-- Cabecera --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Configuración SMTP de la Empresa</h1>
                <p class="text-sm text-slate-500">Establece las credenciales del servidor de correo para los envíos automatizados de tu empresa.</p>
            </div>
            <div>
                <a href="{{ route('admin.envios.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver a Envíos
                </a>
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

        {{-- Formulario Principal --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Credenciales del Servidor SMTP
                </h2>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.smtp.update') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Host SMTP:</label>
                            <input type="text" name="mail_host" value="{{ $smtp->mail_host ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ej: smtp.mailtrap.io o smtp.gmail.com" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Puerto:</label>
                            <input type="number" name="mail_port" value="{{ $smtp->mail_port ?? '587' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ej: 587 o 465" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Usuario SMTP:</label>
                            <input type="text" name="mail_username" value="{{ $smtp->mail_username ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tu usuario o correo de acceso" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña SMTP:</label>
                            <input type="password" name="mail_password" value="{{ $smtp->mail_password ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tu contraseña o clave de aplicación" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Encriptación:</label>
                            <input type="text" name="mail_encryption" value="{{ $smtp->mail_encryption ?? 'tls' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="tls / ssl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Correo Remitente ("De"):</label>
                            <input type="email" name="mail_from_address" value="{{ $smtp->mail_from_address ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="remitente@tudominio.com" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del Remitente:</label>
                            <input type="text" name="mail_from_name" value="{{ $smtp->mail_from_name ?? '' }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nombre de tu Empresa" required>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="py-2.5 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition shadow-sm">
                            Guardar Configuración SMTP
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection