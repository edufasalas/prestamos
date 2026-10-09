@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-[100dvh] overflow-hidden relative w-full bg-gray-100">
    
    <!-- Topbar Corporativo -->
    @php
        $currentUser = auth()->user();
        $userExt = $currentUser ? \App\Models\UserExtension::where('user_id', $currentUser->id)->first() : null;
        $sipExtension = $userExt ? $userExt->extension_number : 'N/A';
        
        // LA VISTA SOLO LEE EL ESTATUS DE LA TABLA:
        // Si no existe el registro o el estatus NO es exactamente 'active' (es 'inactive' o null), se bloquea.
        $isInactive = (!$extensionRecord || $extensionRecord->status !== 'active');
    @endphp

    @include('layouts.topbar', [
        'showSoftphone' => true, 
        'title' => 'WEBPhone',
        'sipExtension' => $sipExtension
    ])

    <!-- Contenedor Principal -->
    <div class="flex-1 w-full h-full p-2 sm:p-4 flex items-center justify-center overflow-hidden">
        
        @if($isInactive)
            <!-- ALERTA DE SERVICIO SUSPENDIDO (No abre FOP2) -->
            <div class="w-full max-w-xl p-8 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl text-center space-y-5">
                <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-2xl flex items-center justify-center mx-auto text-2xl animate-pulse">
                    <i class="fa-solid fa-phone-slash"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-black text-white uppercase tracking-wider">Servicio Temporalmente Suspendido</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        La bolsa de minutos de la compañía se ha agotado o la extensión se encuentra inactiva. El panel de operador (FOP2) está desactivado.
                    </p>
                </div>
                <div class="pt-2">
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-xl text-xs font-bold uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        Estado: Inactivo (Sin Saldo)
                    </span>
                </div>
            </div>
        @else
            <!-- FOP2 NORMAL (Solo se ve si el status es 'active') -->
            <div class="w-full h-full max-w-full max-h-full bg-white rounded-xl shadow-md overflow-hidden relative flex flex-col">
                <iframe 
                    id="fop2-frame"
                    src="https://servidores.voiparkas.com/fop2/?exten={{ $extension }}&pass={{ $pass }}" 
                    class="w-full h-full border-0 absolute inset-0"
                    allow="microphone; autoplay; fullscreen; display-capture">
                </iframe>
            </div>
        @endif

    </div>
</div>
@endsection