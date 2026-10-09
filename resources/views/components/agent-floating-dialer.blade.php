<div x-data="{ 
    openDialerModal: false, 
    dialerUrl: '', 
    loading: false,
    openDialer() {
        this.loading = true;
        this.openDialerModal = true;
        
        // Petición AJAX para obtener el token temporal desde nuestro SoftphoneController
        fetch('{{ route('voip.marcadorLibre') }}')
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    this.dialerUrl = data.url_marcador;
                } else {
                    alert(data.error || 'No se pudo inicializar el softphone.');
                    this.openDialerModal = false;
                }
                this.loading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión con el servidor.');
                this.loading = false;
                this.openDialerModal = false;
            });
    }
}" class="relative z-50">

    <!-- BOTÓN FLOTANTE ANIMADO -->
    <button @click="openDialer()" 
        class="fixed bottom-6 right-6 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer group focus:outline-none"
        title="Abrir Marcador SIP">
        <!-- Efecto de ondas animadas -->
        <span class="absolute w-full h-full rounded-full bg-indigo-400 opacity-75 animate-ping group-hover:animate-none"></span>
        <i class="fa-solid fa-headset text-xl relative z-10"></i>
    </button>

    <!-- MODAL / CONTENEDOR FLOTANTE DEL MARCADOR -->
    <div x-show="openDialerModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed bottom-4 right-6 w-[340px] h-[480px] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col" 
         style="display: none;">
        
        <!-- Cabecera del Modal -->
        <div class="bg-slate-900 text-white px-4 py-3 flex justify-between items-center">
            <span class="text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-phone-volume text-emerald-400"></i> Marcador SIP Agente
            </span>
            <button @click="openDialerModal = false; dialerUrl = ''" class="text-slate-400 hover:text-white cursor-pointer p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Contenido (Iframe cargando el marcador externo con su token) -->
        <div class="flex-1 relative bg-slate-50 flex items-center justify-center">
            <!-- Spinner de carga mientras se pide el token -->
            <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 z-10 space-y-2">
                <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-2xl"></i>
                <span class="text-xs font-medium text-slate-500">Conectando línea SIP...</span>
            </div>

            <!-- Iframe del marcador -->
            <!-- Iframe del marcador con permisos de micrófono y autoplay habilitados -->
            <template x-if="dialerUrl">
                <iframe :src="dialerUrl" allow="microphone; autoplay" class="w-full h-full border-0"></iframe>
            </template>
        </div>
    </div>
</div>