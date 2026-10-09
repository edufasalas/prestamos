<div id="softphone-drawer" class="fixed right-0 top-0 h-full w-[340px] bg-white shadow-2xl border-l border-slate-200 z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
    
    <div class="bg-[#00a884] text-white px-5 py-4 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xs">
                {{ substr($sipExtension ?? '99999', -2) }}
            </div>
            <div>
                <h1 class="text-xs font-semibold tracking-wide">Softphone SIP Enterprise</h1>
                <p class="text-[10px] text-emerald-100 flex items-center gap-1">
                    <span id="status-dot" class="w-2 h-2 bg-amber-300 rounded-full inline-block"></span>
                    <span id="sip-status">Conectando...</span>
                </p>
            </div>
        </div>
        <button onclick="toggleSoftphone()" class="text-white/80 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors" title="Cerrar Panel">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>

    <div id="call-banner" class="bg-[#efeae2] px-5 py-2.5 border-b border-slate-200 flex items-center justify-between text-xs text-slate-600 shrink-0">
        <span id="call-duration" class="font-medium flex items-center gap-1.5 text-[11px]">
            <i class="fa-solid fa-shield-halved text-[#00a884]"></i> Listo para discar
        </span>
        <span class="text-[9px] bg-white px-2 py-0.5 rounded border border-slate-300 font-semibold text-slate-500">Secure</span>
    </div>

    <div class="p-5 bg-white flex flex-col justify-center shrink-0">
        <div class="relative flex items-center bg-[#f0f2f5] rounded-xl px-4 py-2.5 border border-transparent focus-within:border-[#00a884] transition-all">
            <input id="display" readonly placeholder="Número..." class="w-full text-center text-xl font-semibold tracking-wider bg-transparent border-none outline-none text-slate-800 placeholder:text-slate-400 placeholder:text-xs">
            <button onclick="deleteLastDigit()" class="text-slate-400 hover:text-slate-600 p-1.5 transition-colors absolute right-3" title="Borrar">
                <i class="fa-solid fa-delete-left text-base"></i>
            </button>
        </div>
    </div>

    <div id="in-call-controls" class="hidden px-5 py-2 bg-slate-50 border-y border-slate-200 flex justify-center gap-4 shrink-0">
        <button id="btnMute" onclick="toggleMute()" class="w-9 h-9 rounded-full bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 flex items-center justify-center text-xs shadow-2xs transition-colors" title="Silenciar">
            <i class="fa-solid fa-microphone"></i>
        </button>
        <button onclick="openTransferModal()" class="w-9 h-9 rounded-full bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 flex items-center justify-center text-xs shadow-2xs transition-colors" title="Transferir">
            <i class="fa-solid fa-right-left"></i>
        </button>
    </div>

    <div class="flex-1 px-5 py-2 flex flex-col justify-center items-center overflow-y-auto">
        <div class="grid grid-cols-3 gap-3 w-full max-w-[240px] justify-items-center">
            <button type="button" class="whatsapp-btn" onclick="appendDigit('1')">1</button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('2')">2<span class="sub-key">ABC</span></button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('3')">3<span class="sub-key">DEF</span></button>
            
            <button type="button" class="whatsapp-btn" onclick="appendDigit('4')">4<span class="sub-key">GHI</span></button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('5')">5<span class="sub-key">JKL</span></button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('6')">6<span class="sub-key">MNO</span></button>
            
            <button type="button" class="whatsapp-btn" onclick="appendDigit('7')">7<span class="sub-key">PQRS</span></button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('8')">8<span class="sub-key">TUV</span></button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('9')">9<span class="sub-key">WXYZ</span></button>
            
            <button type="button" class="whatsapp-btn" onclick="appendDigit('*')">*</button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('0')">0<span class="sub-key">+</span></button>
            <button type="button" class="whatsapp-btn" onclick="appendDigit('#')">#</button>
        </div>
    </div>

    <div class="p-5 flex justify-center items-center shrink-0 bg-white border-t border-slate-100">
        <button type="button" id="btnLlamar" onclick="makeCall()" class="w-14 h-14 rounded-full bg-[#00a884] hover:bg-[#008f72] text-white flex items-center justify-center shadow-md shadow-emerald-700/30 transition-transform active:scale-90 group" title="Llamar">
            <i class="fa-solid fa-phone text-lg group-hover:rotate-12 transition-transform"></i>
        </button>
        <button type="button" id="btnColgar" onclick="hangUp()" class="w-14 h-14 rounded-full bg-[#ea4335] hover:bg-[#d93025] text-white hidden items-center justify-center shadow-md shadow-rose-700/30 transition-transform active:scale-90 group" title="Colgar">
            <i class="fa-solid fa-phone-slash text-lg group-hover:-rotate-12 transition-transform"></i>
        </button>
    </div>
</div>

<style>
    .whatsapp-btn {
        width: 48px;
        height: 48px;
        border-radius: 50% !important;
        background: #f0f2f5;
        border: none;
        font-size: 1.05rem;
        font-weight: 500;
        color: #111b21;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.1s ease;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        aspect-ratio: 1 / 1;
    }
    .whatsapp-btn:hover { background: #e9edef; }
    .whatsapp-btn:active { transform: scale(0.92); background: #d8dadf; }
    .sub-key {
        font-size: 7px;
        color: #667781;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-top: -3px;
        line-height: 1;
    }
</style>