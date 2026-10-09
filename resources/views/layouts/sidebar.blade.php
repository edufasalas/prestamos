<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 text-white flex flex-col transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none -translate-x-full lg:translate-x-0 border-r border-white/15 select-none" style="background-color: {{ $globalCompany->primary_color ?? '#2052F7' }};">    
    <!-- Cabecera Profesional con Logo Condicional Estricto -->
    <div class="h-20 px-5 flex items-center justify-between border-b border-white/10 bg-black/15 shrink-0">
        <div class="flex items-center gap-3 overflow-hidden w-full">
            @if(!empty($globalCompany->logo_path) && file_exists(public_path($globalCompany->logo_path)))
                <!-- Si existe el logo: solo la imagen limpia, cero textos -->
                <div class="w-full flex items-center py-1">
                    <img src="{{ url('public/' . $globalCompany->logo_path) }}" alt="Logo" class="w-16 max-h-16 w-auto object-contain">
                </div>
            @else
                <!-- Si NO existe el logo: Cuadrito con icono VoIP + Nombre de la empresa -->
                <div class="flex items-center gap-3 w-full">
                    <div class="w-11 h-11 rounded-xl p-2 flex items-center justify-center shrink-0 border border-white/20 bg-white/10 shadow-inner">
                        <i class="fa-solid fa-headset text-white text-base"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="block text-sm font-black tracking-wider uppercase text-white truncate">{{ $globalCompany->name ?? 'Plataforma' }}</span>
                        <span class="block text-[10px] font-semibold tracking-wide uppercase text-blue-200/95">Enterprise Cloud</span>
                    </div>
                </div>
            @endif
        </div>
        
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl transition-all active:scale-95 text-blue-200 hover:text-white hover:bg-white/10 shrink-0">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <!-- Módulos Operativos (Scroll Área) -->
    <div class="flex-1 px-3.5 py-6 space-y-1.5 overflow-y-auto custom-sidebar-scroll">
        
        <p class="sidebar-text px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-white/60">Módulos Operativos</p>

        <!-- Métricas & Reportes -->
        @php $active = request()->routeIs('admin.leads.m*'); @endphp
        <a href="{{ route('admin.leads.metrics') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Reportes">
            <i class="fa-solid fa-chart-line w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
            <span class="sidebar-text whitespace-nowrap">Métricas & Reportes</span>
        </a>
        <!-- Gestion Cliente -->
        @php $active = request()->routeIs('admin.leads.i*'); @endphp
        <a href="{{ route('admin.leads.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Historial">
            <i class="fa-solid fa-users w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
            <span class="sidebar-text whitespace-nowrap">Gestión Clientes</span>
        </a>
        <!-- Historial de Llamadas 
        @php $active = request()->routeIs('agent.cdr*'); @endphp
        <a href="{{ route('agent.cdr.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Historial">
            <i class="fa-solid fa-clock-rotate-left w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
            <span class="sidebar-text whitespace-nowrap">Historial de Llamadas</span>
        </a>


        @php $active = request()->routeIs('agent.fop2*'); @endphp
        <a href="{{ route('agent.fop2.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Audio">
            <i class="fa-solid fa-sliders w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
            <span class="sidebar-text whitespace-nowrap">WEBphone</span>
        </a>             

        <p class="sidebar-text px-3 pt-6 pb-2 text-[10px] font-bold uppercase tracking-wider text-white/60">Configuración</p>
        -->
  

        <!-- MÓDULOS DE SUPER ADMINISTRADOR -->
        @if(auth()->check() && (auth()->user()->role === 'super_admin' || auth()->user()->role == 1))
            <!-- Dashboard 
            @php $active = request()->routeIs('admin.pbx.companypbx*'); @endphp
            <a href="{{ route('admin.pbx.companypbx') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Dashboard">
                <i class="fa-solid fa-chart-pie w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
                <span class="sidebar-text whitespace-nowrap">Dashboard</span>
            </a>          
            -->
            <!-- Configurar Branding 
            @php $active = request()->routeIs('superadmin.companies*'); @endphp
            <a href="{{ route('superadmin.companies.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Configurar Compañias">
                <i class="fa-solid fa-palette w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
                <span class="sidebar-text whitespace-nowrap">Configurar Compáñias</span>
            </a>  
            -->
        @endif
        
        <!-- MÓDULOS DE ADMINISTRADOR -->
        @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role == 2))
            <!-- Dashboard -->
            @php $active = request()->routeIs('admin.pbx.companypbx*'); @endphp
            <a href="{{ route('admin.pbx.companypbx') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Dashboard">
                <i class="fa-solid fa-chart-pie w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
                <span class="sidebar-text whitespace-nowrap">PBX</span>
            </a>        
            <!-- Gestionar Usuarios -->
            @php $active = request()->routeIs('agent.users*'); @endphp
            <a href="{{ route('agent.users.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Gestionar Usuarios">
                <i class="fa-solid fa-users w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
                <span class="sidebar-text whitespace-nowrap">Gestión Usuarios</span>
            </a>

            <!-- Configurar Branding -->
            @php $active = request()->routeIs('admin.branding*'); @endphp
            <a href="{{ route('admin.branding.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Configurar Branding">
                <i class="fa-solid fa-palette w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
                <span class="sidebar-text whitespace-nowrap">Configurar Branding</span>
            </a>  
            <!-- Configurar correos -->
            @php $active = request()->routeIs('admin.envios*'); @endphp
            <a href="{{ route('admin.envios.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Configurar Correos">
                <i class="fa-solid fa-envelope w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
                <span class="sidebar-text whitespace-nowrap">Configurar Correos</span>
            </a>              
        @endif        
        

        <!-- Audio Config 
        @php $active = request()->routeIs('agent.audio*'); @endphp
        <a href="{{ route('agent.audio.index') }}" class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl font-semibold text-xs transition-all duration-150 active:scale-[0.98] group {{ $active ? 'bg-white/20 text-white border-l-4 border-white shadow-sm' : 'text-blue-100/80 hover:bg-black/15 hover:text-white' }}" title="Audio">
            <i class="fa-solid fa-sliders w-4 text-center shrink-0 transition-transform group-hover:scale-110 {{ $active ? 'text-white' : 'text-blue-200 group-hover:text-white' }}"></i> 
            <span class="sidebar-text whitespace-nowrap">Audio Config</span>
        </a>     
        -->
    </div>

    <!-- Pie de Página (Estado de Agente & Extensión) -->
    <div class="p-4 border-t border-white/10 bg-black/20 flex items-center justify-between shrink-0">
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
        <button onclick="cerrarSesion();" class="p-2.5 rounded-xl transition-all active:scale-90 text-blue-200 hover:text-rose-300 hover:bg-rose-500/20" title="Cerrar Sesión"> Cerrar Sesión
            <i class="fa-solid fa-power-off text-sm"></i>
        </button>
    </div>
</aside>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        function cerrarSesion() {
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "Saldrás del master control.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4239bf',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }      
</script>
<style>
    .custom-sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 9999px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }
</style>