@extends('layouts.app')

@section('content')
<div class="flex-1 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full">
    
    <!-- Topbar Corporativo -->
    @include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Directorio Empresarial'])

    <!-- Contenido Principal con Scroll -->
    <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Encabezado de Sección -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Directorio Corporativo</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Consulta extensiones internas, estados de presencia de agentes y marcación directa.</p>
                    </div>
                </div>
                <!-- Buscador rápido -->
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="directory-search" placeholder="Buscar por nombre o extensión..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-slate-900 focus:border-slate-900 outline-none transition-all">
                </div>
            </div>

            <!-- Grilla Dinámica de Agentes y Extensiones -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="contacts-grid">
                
                @forelse($agents as $agent)
                    @php
                        // Obtenemos su extensión asociada
                        $extObj = $agent->extensions()->latest()->first();
                        $extNumber = $extObj ? $extObj->extension_number : 'Sin Ext';
                        
                        // Generamos las iniciales para el avatar de forma segura
                        $nameParts = explode(' ', trim($agent->name));
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    @endphp

                    <div class="contact-card bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-4"
                         data-name="{{ strtolower($agent->name) }}" data-ext="{{ $extNumber }}">
                        <div class="flex items-center gap-3.5 overflow-hidden">
                            <div class="relative shrink-0">
                                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm">
                                    {{ $initials }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="truncate">
                                <h3 class="text-xs font-bold text-slate-900 truncate">{{ $agent->name }}</h3>
                                <p class="text-[11px] text-slate-500 font-medium">{{ $agent->email ?? 'Agente Interno' }}</p>
                                <span class="inline-block mt-1 text-[10px] font-mono font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Ext. {{ $extNumber }}</span>
                            </div>
                        </div>
                        @if($extObj)
                            <button onclick="callAgent('{{ $extNumber }}')" class="w-9 h-9 rounded-xl bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white flex items-center justify-center transition-all shrink-0 active:scale-95" title="Llamar a Ext. {{ $extNumber }}">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full text-center py-10 text-slate-400 text-xs font-medium">
                        No hay agentes registrados en el sistema.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</div>

<script>
   function callAgent(extension) {
        // 1. Mostrar el cajón del softphone si está cerrado
        let drawer = document.getElementById('softphone-drawer');
        if (drawer && drawer.classList.contains('translate-x-full')) {
            drawer.classList.remove('translate-x-full');
        }

        // 2. Escribir la extensión en el input del softphone
        let displayInput = document.getElementById('display');
        if (displayInput) {
            displayInput.value = extension;
        }

        // 3. Simular el clic en el botón verde de llamar del softphone para que inicie la llamada real
        let btnLlamar = document.getElementById('btnLlamar');
        if (btnLlamar) {
            // Pequeño retraso para que se pinte el número antes de disparar la llamada
            setTimeout(() => {
                btnLlamar.click();
            }, 100);
        }
    }

    // Buscador en tiempo real
    document.getElementById('directory-search').addEventListener('input', function(e) {
        let term = e.target.value.toLowerCase();
        let cards = document.querySelectorAll('.contact-card');

        cards.forEach(card => {
            let name = card.getAttribute('data-name');
            let ext = card.getAttribute('data-ext');
            if (name.includes(term) || ext.includes(term)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>
@endsection