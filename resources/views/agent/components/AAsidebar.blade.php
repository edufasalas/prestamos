<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 text-white flex flex-col transition-all duration-300 ease-in-out shadow-2xl lg:shadow-none -translate-x-full lg:translate-x-0" style="background-color: #2052F7 !important; border-right: 1px solid rgba(255, 255, 255, 0.15) !important; color: #ffffff !important;">
    
    <!-- Cabecera Profesional con Logo -->
    <div class="h-20 px-5 flex items-center justify-between border-b border-white/10" style="">
        <div class="flex items-center gap-3 overflow-hidden">
            <!-- Contenedor del Logo -->
            <div class="w-11 h-11 rounded-xl p-2 flex items-center justify-center shrink-0 border border-white/20 shadow-inner" style="">
                
                <!-- LOGO: Cambia 'images/logo.png' si tu imagen está en otra ruta dentro de public/ -->
                <img src="{{ asset('images/logo.png') }}" alt="CallerVoip Logo" class="w-full h-full object-contain" style="filter: brightness(0) invert(1);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                
                <!-- Fallback por si la imagen no se encuentra -->
                <div class="w-full h-full items-center justify-center text-white font-black text-sm" style="display: none;">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>
            
            <div class="sidebar-text transition-opacity duration-200">
                <span class="block text-sm font-black tracking-wider uppercase text-white truncate" style="color: #ffffff !important;">CallerVoip</span>
                <span class="block text-[10px] font-semibold tracking-wide uppercase" style="color: rgba(191, 219, 254, 0.9) !important;">Enterprise Cloud</span>
            </div>
        </div>
        
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg transition-colors hover:bg-black/20" style="color: #bfdbfe !important;">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <!-- Módulos Operativos -->
    <div class="flex-1 px-3.5 py-6 space-y-1.5 overflow-y-auto custom-sidebar-scroll">
        <p class="sidebar-text px-3 pb-2 text-[10px] font-bold uppercase tracking-wider" style="color: rgba(255, 255, 255, 0.6) !important;">Módulos Operativos</p>
        
        <a href="#" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all border-l-2 border-white shadow-sm group" style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important;" title="Dashboard">
            <i class="fa-solid fa-chart-pie w-4 text-center shrink-0 text-white"></i> 
            <span class="sidebar-text whitespace-nowrap">Dashboard</span>
        </a>
        <a href="#" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-xs transition-all group hover:bg-black/20" style="color: #bfdbfe !important;" title="Directorio">
            <i class="fa-solid fa-address-book w-4 text-center shrink-0"></i> 
            <span class="sidebar-text whitespace-nowrap">Directorio</span>
        </a>
        <a href="{{ route('agent.cdr.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-xs transition-all group hover:bg-black/20" style="color: #bfdbfe !important;" title="Historial">
            <i class="fa-solid fa-clock-rotate-left w-4 text-center shrink-0"></i> 
            <span class="sidebar-text whitespace-nowrap">Historial de Llamadas</span>
        </a>
        <a href="#" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-xs transition-all group hover:bg-black/20" style="color: #bfdbfe !important;" title="Reportes">
            <i class="fa-solid fa-chart-line w-4 text-center shrink-0"></i> 
            <span class="sidebar-text whitespace-nowrap">Métricas & Reportes</span>
        </a>

        <p class="sidebar-text px-3 pt-6 pb-2 text-[10px] font-bold uppercase tracking-wider" style="color: rgba(255, 255, 255, 0.6) !important;">Configuración</p>
        <a href="#" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-xs transition-all group hover:bg-black/20" style="color: #bfdbfe !important;" title="Audio">
            <i class="fa-solid fa-sliders w-4 text-center shrink-0"></i> 
            <span class="sidebar-text whitespace-nowrap">Audio Config</span>
        </a>
        
        <a href="{{ route('agent.extensions.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-medium text-xs transition-all group hover:bg-black/20" style="color: #bfdbfe !important;" title="Configurar Extensiones">
            <i class="fa-solid fa-phone-alt w-4 text-center shrink-0"></i> 
            <span class="sidebar-text whitespace-nowrap">Configurar Extensión</span>
        </a>
    </div>

    <!-- Pie de página -->
    <div class="p-4 border-t border-white/10 flex items-center justify-between" style="background-color: rgba(0, 0, 0, 0.2) !important;">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 rounded-xl font-bold text-xs flex items-center justify-center border border-white/20 shrink-0 shadow-inner text-white" style="background-color: rgba(0, 0, 0, 0.35) !important;">
                {{ substr($sipExtension ?? '99', -2) }}
            </div>
            <div class="sidebar-text transition-opacity duration-200">
                <p class="text-xs font-bold text-white whitespace-nowrap" style="color: #ffffff !important;">Ext. {{ $sipExtension ?? '99999' }}</p>
                <p class="text-[10px] font-semibold flex items-center gap-1.5 whitespace-nowrap" style="color: #bfdbfe !important;">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse shadow-sm shadow-emerald-500"></span> En línea
                </p>
            </div>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <button onclick="document.getElementById('logout-form').submit();" class="p-2 rounded-xl transition-colors shrink-0 hover:bg-black/20 hover:text-rose-300" style="color: #bfdbfe !important;" title="Cerrar Sesión">
            <i class="fa-solid fa-power-off text-sm"></i>
        </button>
    </div>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/50 z-40 hidden lg:hidden transition-opacity backdrop-blur-xs"></div>

<style>
    body.sidebar-collapsed #app-sidebar {
        width: 76px !important;
    }
    body.sidebar-collapsed #app-sidebar .sidebar-text {
        opacity: 0;
        pointer-events: none;
        display: none;
    }
    body.sidebar-collapsed main {
        margin-left: 76px !important;
    }

    .custom-sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 4px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
</style>