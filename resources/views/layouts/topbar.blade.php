<header class="h-18 backdrop-blur-md border-b border-slate-200/70 px-6 lg:px-8 flex justify-between items-center shrink-0 z-20 shadow-xs" style="background-color: color-mix(in srgb, {{ $globalCompany->primary_color ?? '#2052F7' }} 10%, transparent);">
    <div class="flex items-center gap-4">
        <!-- Botón Hamburguesa -->
        <button onclick="toggleSidebar()" class="text-slate-500 hover:text-slate-800 p-2 rounded-xl hover:bg-slate-100/80 transition-all cursor-pointer border border-slate-200/60 shadow-2xs" title="Alternar Menú">
            <i class="fa-solid fa-bars text-base"></i>
        </button>
        
        <div>
            <h1 class="text-sm lg:text-base font-extrabold text-slate-900 tracking-tight">
                {{ $title ?? 'Centro de Operaciones SIP' }}
            </h1>
            <p class="text-[11px] text-slate-500 font-medium">
                Simulador: <span class="text-slate-800 font-bold">{{ $sipExtension ?? 'N/A' }}</span>
            </p>
        </div>
    </div>

    <!-- Bloque Derecho: Softphone y Perfil de Usuario -->
    <div class="flex items-center gap-3 lg:gap-4">

        <!-- Dropdown de Perfil del Usuario -->
        <div class="relative">
            <button id="user-menu-button"
                    onclick="toggleUserMenu(event)"
                    type="button"
                    class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-100/80 transition-all cursor-pointer border border-transparent hover:border-slate-200/60 group">
            
                <!-- Icono de Usuario / Avatar -->
                <div class="w-9 h-9 rounded-xl text-white flex items-center justify-center font-bold shadow-md shrink-0 group-hover:scale-105 transition-transform"
                     style="background-color: {{ $globalCompany->primary_color ?? '#2052F7' }};">
                    <i class="fa-solid fa-user text-sm"></i>
                </div>

                <!-- Detalles: Nombre y Rol -->
                <div class="text-left hidden md:block">
                    <span class="block text-xs font-extrabold text-slate-800 leading-tight">
                        {{ auth()->user()->name ?? 'Usuario SIP' }}
                    </span>
                    <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border mt-0.5"
                          style="color: {{ $globalCompany->primary_color ?? '#2052F7' }}; background-color: {{ ($globalCompany->primary_color ?? '#2052F7') . '10' }}; border-color: {{ ($globalCompany->primary_color ?? '#2052F7') . '20' }};">
                        {{ auth()->user()->role ?? 'Agente' }}
                    </span>
                </div>

                <i id="user-menu-chevron" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200 ml-1"></i>
            </button>

            <!-- Menú Desplegable -->
            <div id="user-dropdown-menu"
                 class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-slate-100 py-2 z-50 divide-y divide-slate-100">
                
                <!-- Encabezado del Dropdown con Branding -->
                <div class="px-4 py-3 bg-slate-50/50">
                    <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                        <a href="{{ route('agent.profile.edit') }}">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md text-white shadow-xs truncate max-w-[130px]"
                              style="background-color: {{ $globalCompany->primary_color ?? '#2052F7' }};">
                              Perfil
                        </span>
                            <p class="text-xs font-black text-slate-900 truncate">{{ auth()->user()->name ?? 'Usuario SIP' }}</p>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ auth()->user()->email ?? 'agente@callervoip.com' }}</p>                        
                        </a> 
                    </div>
                </div>


                <!-- Cerrar Sesión -->
                <div class="py-1">
                    <button onclick="cerrarSesion();" 
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer group">
                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 group-hover:bg-rose-100 transition-colors">
                            <i class="fa-solid fa-power-off text-xs"></i>
                        </div>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</header>
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
<!-- Script para controlar el menú desplegable -->
<script>
function toggleUserMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('user-dropdown-menu');
    const chevron = document.getElementById('user-menu-chevron');
    if (menu) {
        menu.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-180');
        }
    }
}

document.addEventListener('click', function(event) {
    const button = document.getElementById('user-menu-button');
    const menu = document.getElementById('user-dropdown-menu');
    const chevron = document.getElementById('user-menu-chevron');
    
    if (button && menu && !button.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
        if (chevron) {
            chevron.classList.remove('rotate-180');
        }
    }
});
</script>