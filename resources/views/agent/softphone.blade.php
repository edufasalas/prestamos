<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Softphone WhatsApp Style - CallerVoip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- JsSIP CDN -->
    <script src="https://cdn.jsdelivr.net/npm/jssip/dist/jssip.min.js"></script>
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#f0f2f5] text-slate-800 h-screen w-screen flex justify-center items-center font-sans overflow-hidden m-0 p-2">

    <!-- Contenedor Principal Adaptativo (Sin cortes y perfectamente proporcionado) -->
    <div class="w-full max-w-[340px] bg-white rounded-2xl shadow-xl border border-slate-200/80 flex flex-col max-h-[95vh] overflow-hidden relative">
        
        <!-- Header Estilo WhatsApp -->
        <div class="bg-[#00a884] text-white px-4 py-3 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xs">
                    {{ substr($sipExtension ?? '77777', -2) }}
                </div>
                <div>
                    <h1 class="text-xs font-semibold tracking-wide">Panel Telefónico</h1>
                    <p class="text-[10px] text-emerald-100 flex items-center gap-1">
                        <span id="status-dot" class="w-2 h-2 bg-amber-300 rounded-full inline-block"></span>
                        <span id="sip-status">Conectando...</span>
                    </p>
                </div>
            </div>
            
            <div class="bg-white/10 px-2.5 py-0.5 rounded-full text-[10px] font-medium text-white border border-white/20">
                Ext: {{ $sipExtension ?? '99999' }}
            </div>
        </div>

        <!-- Barra de Estado / Indicador -->
        <div id="call-banner" class="bg-[#efeae2] px-4 py-2 border-b border-slate-200 flex items-center justify-between text-xs text-slate-600 shrink-0">
            <span id="call-duration" class="font-medium flex items-center gap-1.5 text-[11px]">
                <i class="fa-solid fa-shield-halved text-[#00a884]"></i> Listo para discar
            </span>
            <span class="text-[9px] bg-white px-2 py-0.5 rounded border border-slate-300 font-semibold text-slate-500">SIP</span>
        </div>

        <!-- Pantalla de Marcación -->
        <div class="px-4 py-3 bg-white flex flex-col justify-center shrink-0">
            <div class="relative flex items-center bg-[#f0f2f5] rounded-xl px-3 py-2.5 border border-transparent focus-within:border-[#00a884] transition-all">
                <input id="display" readonly placeholder="Número..." class="w-full text-center text-xl font-semibold tracking-wider bg-transparent border-none outline-none text-slate-800 placeholder:text-slate-400 placeholder:text-xs">
                <button onclick="deleteLastDigit()" class="text-slate-400 hover:text-slate-600 p-1.5 transition-colors absolute right-2" title="Borrar">
                    <i class="fa-solid fa-delete-left text-base"></i>
                </button>
            </div>
        </div>

        <!-- Teclado Numérico con botones 100% circulares y sin desbordes -->
        <div class="px-4 py-1 flex flex-col justify-center items-center shrink-0">
            <div class="grid grid-cols-3 gap-2.5 w-full max-w-[260px] justify-items-center">
                <button class="whatsapp-btn" onclick="pressKey('1')">1</button>
                <button class="whatsapp-btn" onclick="pressKey('2')">2<span class="sub-key">ABC</span></button>
                <button class="whatsapp-btn" onclick="pressKey('3')">3<span class="sub-key">DEF</span></button>
                
                <button class="whatsapp-btn" onclick="pressKey('4')">4<span class="sub-key">GHI</span></button>
                <button class="whatsapp-btn" onclick="pressKey('5')">5<span class="sub-key">JKL</span></button>
                <button class="whatsapp-btn" onclick="pressKey('6')">6<span class="sub-key">MNO</span></button>
                
                <button class="whatsapp-btn" onclick="pressKey('7')">7<span class="sub-key">PQRS</span></button>
                <button class="whatsapp-btn" onclick="pressKey('8')">8<span class="sub-key">TUV</span></button>
                <button class="whatsapp-btn" onclick="pressKey('9')">9<span class="sub-key">WXYZ</span></button>
                
                <button class="whatsapp-btn" onclick="pressKey('*')">*</button>
                <button class="whatsapp-btn" onclick="pressKey('0')">0<span class="sub-key">+</span></button>
                <button class="whatsapp-btn" onclick="pressKey('#')">#</button>
            </div>
        </div>

        <!-- Botones de Acción (Llamar / Colgar) -->
        <div class="px-4 py-3 flex justify-center items-center shrink-0 bg-white">
            <button id="btnLlamar" onclick="makeCall()" class="w-14 h-14 rounded-full bg-[#00a884] hover:bg-[#008f72] text-white flex items-center justify-center shadow-lg shadow-emerald-700/30 transition-transform active:scale-90 group" title="Llamar (Enter)">
                <i class="fa-solid fa-phone text-lg group-hover:rotate-12 transition-transform"></i>
            </button>
            <button id="btnColgar" onclick="hangUp()" class="w-14 h-14 rounded-full bg-[#ea4335] hover:bg-[#d93025] text-white hidden items-center justify-center shadow-lg shadow-rose-700/30 transition-transform active:scale-90 group" title="Colgar (Escape)">
                <i class="fa-solid fa-phone-slash text-lg group-hover:-rotate-12 transition-transform"></i>
            </button>
        </div>

        <!-- Footer -->
        <div class="bg-[#f0f2f5] px-4 py-2 text-center text-[10px] text-slate-500 border-t border-slate-200/60 shrink-0">
            CallerVoip • <span class="text-[#00a884] font-medium">En línea</span>
        </div>

    </div>

    <!-- Estilos CSS con proporciones estrictas para asegurar círculos perfectos -->
    <style>
        .whatsapp-btn {
            width: 46px;
            height: 46px;
            border-radius: 50% !important;
            background: #f0f2f5;
            border: none;
            font-size: 1.1rem;
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
        .whatsapp-btn:hover {
            background: #e9edef;
        }
        .whatsapp-btn:active {
            transform: scale(0.92);
            background: #d8dadf;
        }
        .sub-key {
            font-size: 7px;
            color: #667781;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: -3px;
            line-height: 1;
        }
    </style>

    <!-- Recursos de Audio -->
    <audio id="remoteAudio" autoplay></audio>

    <script>
        const SIP_USER = "{{ $sipExtension ?? '99999' }}";
        const SIP_PASS = "{{ $sipPassword ?? '032c2236ee397c75dfacc7646dcbf3c4' }}";
        const SIP_WS = "wss://servidores.voiparkas.com:8089/ws";

        let ua, session = null, dialedNumber = '';
        const display = document.getElementById('display');
        const statusLabel = document.getElementById('sip-status');
        const statusDot = document.getElementById('status-dot');
        const callDurationLabel = document.getElementById('call-duration');
        const callBanner = document.getElementById('call-banner');

        function playDtmfTone() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.value = 900;
                gain.gain.setValueAtTime(0.04, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.1);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.1);
            } catch(e) {}
        }

        function pressKey(digit) {
            playDtmfTone();
            dialedNumber += digit;
            display.value = dialedNumber;

            if (session && session.isEstablished()) {
                session.sendDTMF(digit);
            }
        }

        function deleteLastDigit() {
            dialedNumber = dialedNumber.slice(0, -1);
            display.value = dialedNumber;
        }

        document.addEventListener('keydown', function(e) {
            if (e.key >= '0' && e.key <= '9' || e.key === '*' || e.key === '#') {
                pressKey(e.key);
            } else if (e.key === 'Backspace') {
                deleteLastDigit();
            } else if (e.key === 'Enter') {
                if (document.getElementById('btnLlamar').classList.contains('hidden') === false) {
                    makeCall();
                }
            } else if (e.key === 'Escape') {
                hangUp();
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            let socket = new JsSIP.WebSocketInterface(SIP_WS);
            let configuration = {
                sockets: [socket],
                uri: 'sip:' + SIP_USER + '@servidores.voiparkas.com',
                password: SIP_PASS
            };

            ua = new JsSIP.UA(configuration);

            ua.on('registered', function(e) {
                statusLabel.textContent = 'En línea';
                statusDot.className = 'w-2 h-2 bg-emerald-300 rounded-full inline-block';
                callDurationLabel.innerHTML = '<i class="fa-solid fa-circle-check text-[#00a884]"></i> Conectado al servidor SIP';
            });

            ua.on('registrationFailed', function(e) {
                statusLabel.textContent = 'Sin conexión';
                statusDot.className = 'w-2 h-2 bg-rose-400 rounded-full inline-block';
                callDurationLabel.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Error de registro SIP';
            });

            ua.start();
        });

        function makeCall() {
            if (!dialedNumber) return;
            
            if (session) {
                try { session.terminate(); } catch(e) {}
                session = null;
            }

            let options = {
                mediaConstraints: { audio: true, video: false },
                pcConfig: { iceServers: [{ urls: ['stun:stun.l.google.com:19302'] }] }
            };

            statusLabel.textContent = 'Marcando...';
            callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-amber-600 animate-pulse"></i> Marcando...';
            callBanner.style.backgroundColor = '#fef3c7';
            session = ua.call('sip:' + dialedNumber + '@servidores.voiparkas.com', options);

            session.on('progress', function(e) {
                statusLabel.textContent = 'Llamando...';
                callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-amber-600 animate-pulse"></i> Llamando...';
                toggleButtons(true);
            });

            session.on('confirmed', function(e) {
                statusLabel.textContent = 'En llamada';
                callDurationLabel.innerHTML = '<i class="fa-solid fa-headset text-[#00a884]"></i> Conversación activa';
                callBanner.style.backgroundColor = '#d1fae5';
                toggleButtons(true);
            });

            session.on('ended', function(e) {
                resetCallState();
            });

            session.on('failed', function(e) {
                resetCallState();
            });

            if (session.connection) {
                session.connection.addEventListener('addstream', function(e) {
                    document.getElementById('remoteAudio').srcObject = e.stream;
                });
            }
        }

        function hangUp() {
            if (session) {
                try {
                    if (session.isEstablished() || session.isInProgress()) {
                        session.terminate();
                    }
                } catch (error) {}
            }
            resetCallState();
        }

        function resetCallState() {
            session = null;
            toggleButtons(false);
            statusLabel.textContent = 'En línea';
            callBanner.style.backgroundColor = '#efeae2';
            callDurationLabel.innerHTML = '<i class="fa-solid fa-shield-halved text-[#00a884]"></i> Listo para discar';
            document.getElementById('remoteAudio').srcObject = null;
        }

        function toggleButtons(inCall) {
            document.getElementById('btnLlamar').classList.toggle('hidden', inCall);
            document.getElementById('btnColgar').classList.toggle('hidden', !inCall);
            document.getElementById('btnColgar').classList.toggle('flex', inCall);
        }
    </script>
</body>
</html>