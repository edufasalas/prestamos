<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Enterprise - CallerVoip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- Headers de seguridad y permisos para WebRTC y Micrófono en móviles -->
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <meta http-equiv="Permissions-Policy" content="microphone=(self)">
    <meta http-equiv="Cross-Origin-Opener-Policy" content="same-origin-allow-popups">    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/jssip/dist/jssip.min.js"></script>
    <!-- Incluimos Chart.js para las estadísticas gráficas profesionales -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        /* REGLAS CSS PARA COLAPSO EN ESCRITORIO (PC) */
        @media (min-width: 1024px) {
            body.sidebar-collapsed #app-sidebar {
                width: 5rem !important; /* Reducir ancho del menú */
            }
            body.sidebar-collapsed #app-sidebar .sidebar-text {
                display: none !important; /* Ocultar textos dentro del menú */
            }
            body.sidebar-collapsed #main-content {
                margin-left: 5rem !important; /* Mover el contenido principal */
            }
        }
    </style>
</head>
<body class="bg-[#f3f4f6] text-slate-800 font-sans h-screen w-screen flex overflow-hidden m-0 p-0">

    @include('layouts.sidebar')

    <!-- Overlay transparente para móviles -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/50 z-40 hidden lg:hidden transition-opacity backdrop-blur-xs"></div>

    <main id="main-content" class="flex-1 lg:ml-64 flex flex-col h-screen overflow-hidden bg-[#f8fafc] relative w-full transition-all duration-300">
        
        <!-- Topbar reutilizable con Softphone activo -->
        @include('layouts.topbar', ['showSoftphone' => true, 'title' => 'Centro de Operaciones'])

        <div class="flex-1 p-6 lg:p-8 overflow-y-auto space-y-6">
            
            <!-- Tarjetas de Métricas Superiores -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Llamadas Hoy</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $callsToday }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tiempo Promedio</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $avgDuration }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Llamadas Exitosas</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $successRate }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between group">
                    <div class="space-y-1">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">WebSocket SIP</p>
                        <h3 id="sip-badge-text" class="text-lg font-extrabold text-amber-500 tracking-tight mt-1">Conectando...</h3>
                        <span id="sip-badge-sub" class="inline-block text-[10px] text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-md border border-amber-100">Sincronizando</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DE GRÁFICOS Y ESTADÍSTICAS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Gráfico de Línea: Comportamiento de llamadas en el día -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs lg:col-span-2 flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Flujo de Llamadas por Hora</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Distribución horaria de actividad de la extensión</p>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-200/60">Tiempo Real</span>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="callsFlowChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Dona: Distribución Entrantes vs Salientes -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="mb-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Tipo de Tráfico</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Entrantes vs Salientes</p>
                    </div>
                    <div class="relative h-52 w-full flex items-center justify-center">
                        <canvas id="callsDistributionChart"></canvas>
                    </div>
                    <div class="mt-4 flex justify-around text-center border-t border-slate-100 pt-3">
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase">Entrantes</span>
                            <span id="lblInboundPct" class="text-sm font-extrabold text-blue-600">0%</span>
                        </div>
                        <div class="border-r border-slate-100"></div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase">Salientes</span>
                            <span id="lblOutboundPct" class="text-sm font-extrabold text-emerald-600">0%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    @include('agent.components.softphone')
    <audio id="remoteAudio" autoplay playsinline></audio>

    <script>
        const SIP_USER = "{{ $sipExtension }}";
        const SIP_PASS = "{{ $sipPassword }}";
        const SIP_WS   = "{{ $sipWsUrl }}";
        const SIP_DOMAIN = "{{ $sipDomain }}";
        
        let ua, session = null, dialedNumber = '', isMuted = false;
        
        const display = document.getElementById('display');
        const statusLabel = document.getElementById('sip-status');
        const statusDot = document.getElementById('status-dot');
        const headerSipDot = document.getElementById('header-sip-dot');
        const callDurationLabel = document.getElementById('call-duration');
        const sipBadgeText = document.getElementById('sip-badge-text');
        const sipBadgeSub = document.getElementById('sip-badge-sub');
        const softphoneDrawer = document.getElementById('softphone-drawer');

        function toggleSidebar() {
            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (window.innerWidth >= 1024) {
                document.body.classList.toggle('sidebar-collapsed');
            } else {
                if (sidebar) sidebar.classList.toggle('-translate-x-full');
                if (overlay) overlay.classList.toggle('hidden');
            }
        }

        function toggleSoftphone() {
            if (softphoneDrawer) softphoneDrawer.classList.toggle('translate-x-full');
        }

        // --- INICIALIZACIÓN DE GRÁFICOS ESTADÍSTICOS CON DATOS 100% REALES ---
        document.addEventListener("DOMContentLoaded", function() {
            const recentCallsData = @json($recentCalls ?? []);

            const hoursLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'];
            let hoursCounts = [0, 0, 0, 0, 0, 0];
            let inboundCount = 0;
            let outboundCount = 0;

            recentCallsData.forEach(call => {
                if (call.type === 'inbound') {
                    inboundCount++;
                } else {
                    outboundCount++;
                }

                if (call.created_at) {
                    let parts = call.created_at.split(' ');
                    if (parts[1]) {
                        let hourPart = parseInt(parts[1].split(':')[0]);
                        if (hourPart >= 8 && hourPart < 10) hoursCounts[0]++;
                        else if (hourPart >= 10 && hourPart < 12) hoursCounts[1]++;
                        else if (hourPart >= 12 && hourPart < 14) hoursCounts[2]++;
                        else if (hourPart >= 14 && hourPart < 16) hoursCounts[3]++;
                        else if (hourPart >= 16 && hourPart < 18) hoursCounts[4]++;
                        else if (hourPart >= 18) hoursCounts[5]++;
                    }
                }
            });

            const totalTraffic = inboundCount + outboundCount;
            const inboundPct = totalTraffic > 0 ? Math.round((inboundCount / totalTraffic) * 100) : 0;
            const outboundPct = totalTraffic > 0 ? Math.round((outboundCount / totalTraffic) * 100) : 0;

            // Actualizar etiquetas visuales de porcentaje con datos reales
            const lblInbound = document.getElementById('lblInboundPct');
            const lblOutbound = document.getElementById('lblOutboundPct');
            if (lblInbound) lblInbound.textContent = inboundPct + '%';
            if (lblOutbound) lblOutbound.textContent = outboundPct + '%';

            // Gráfico de Flujo de Llamadas (Líneas) - Usando exclusivamente data real
            const ctxFlow = document.getElementById('callsFlowChart').getContext('2d');
            new Chart(ctxFlow, {
                type: 'line',
                data: {
                    labels: hoursLabels,
                    datasets: [{
                        label: 'Llamadas',
                        data: hoursCounts, // Sin valores simulados (mock data)
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, precision: 0 } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

            // Gráfico de Distribución (Dona) - Usando exclusivamente data real
            const ctxDist = document.getElementById('callsDistributionChart').getContext('2d');
            new Chart(ctxDist, {
                type: 'doughnut',
                data: {
                    labels: ['Entrantes', 'Salientes'],
                    datasets: [{
                        data: totalTraffic > 0 ? [inboundPct, outboundPct] : [0, 0],
                        backgroundColor: ['#2563eb', '#059669'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } },
                    cutout: '75%'
                }
            });
        });

        // --- TONO DTMF REAL Y BLINDADO (ITU-T Q.23) ---
        let sharedAudioCtx = null;
        
        function playDtmfTone(digit) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!sharedAudioCtx) {
                    sharedAudioCtx = new AudioContext();
                }
                
                if (sharedAudioCtx.state === 'suspended') {
                    sharedAudioCtx.resume();
                }
        
                let lowFreq = 697, highFreq = 1209;
        
                switch(String(digit)) {
                    case '1': lowFreq = 697; highFreq = 1209; break;
                    case '2': lowFreq = 697; highFreq = 1336; break;
                    case '3': lowFreq = 697; highFreq = 1477; break;
                    case '4': lowFreq = 770; highFreq = 1209; break;
                    case '5': lowFreq = 770; highFreq = 1336; break;
                    case '6': lowFreq = 770; highFreq = 1477; break;
                    case '7': lowFreq = 852; highFreq = 1209; break;
                    case '8': lowFreq = 852; highFreq = 1336; break;
                    case '9': lowFreq = 852; highFreq = 1477; break;
                    case '*': lowFreq = 941; highFreq = 1209; break;
                    case '0': lowFreq = 941; highFreq = 1336; break;
                    case '#': lowFreq = 941; highFreq = 1477; break;
                    default: return;
                }
        
                const osc1 = sharedAudioCtx.createOscillator();
                const osc2 = sharedAudioCtx.createOscillator();
                const gainNode = sharedAudioCtx.createGain();
        
                osc1.type = 'sine'; 
                osc1.frequency.setValueAtTime(lowFreq, sharedAudioCtx.currentTime);
                
                osc2.type = 'sine'; 
                osc2.frequency.setValueAtTime(highFreq, sharedAudioCtx.currentTime);
        
                const startTime = sharedAudioCtx.currentTime;
                const duration = 0.15; 
        
                gainNode.gain.setValueAtTime(0.001, startTime);
                gainNode.gain.linearRampToValueAtTime(0.1, startTime + 0.02); 
                gainNode.gain.exponentialRampToValueAtTime(0.001, startTime + duration); 
        
                osc1.connect(gainNode); 
                osc2.connect(gainNode);
                gainNode.connect(sharedAudioCtx.destination);
        
                osc1.start(startTime); 
                osc2.start(startTime);
                
                osc1.stop(startTime + duration);
                osc2.stop(startTime + duration);
        
            } catch(e) {
                console.error("Error reproduciendo tono DTMF:", e);
            }
        }

        function appendDigit(d) {
            playDtmfTone(d);
            dialedNumber += d;
            if (display) display.value = dialedNumber;
            if (session && session.isEstablished()) {
                session.sendDTMF(d);
            }
        }

        function deleteLastDigit() {
            dialedNumber = dialedNumber.slice(0, -1);
            if (display) display.value = dialedNumber;
        }

        document.addEventListener('keydown', function(e) {
            if (softphoneDrawer && !softphoneDrawer.classList.contains('translate-x-full')) {
                if ((e.key >= '0' && e.key <= '9') || e.key === '*' || e.key === '#') {
                    appendDigit(e.key);
                } else if (e.key === 'Backspace') {
                    deleteLastDigit();
                } else if (e.key === 'Enter') {
                    const btnLlamar = document.getElementById('btnLlamar');
                    if (btnLlamar && !btnLlamar.classList.contains('hidden')) makeCall();
                } else if (e.key === 'Escape') {
                    hangUp();
                }
            }
        });

        function connect() {
        if (typeof JsSIP === 'undefined') return;
        
        const socket = new JsSIP.WebSocketInterface(SIP_WS);
        const config = {
            sockets: [socket],
            uri: "sip:" + SIP_USER + "@" + SIP_DOMAIN,
            password: SIP_PASS,
            session_timers: false,
            register_expires: 300 
        };
        
        ua = new JsSIP.UA(config);
    
        ua.on('registered', function(e) {
            if (statusLabel) statusLabel.textContent = 'En línea';
            if (statusDot) statusDot.className = 'w-2 h-2 bg-emerald-400 rounded-full inline-block';
            if (headerSipDot) headerSipDot.className = 'absolute -top-1 -right-1 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full';
            if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-circle-check text-[#00a884]"></i> Conectado a ' + SIP_DOMAIN;
            if (sipBadgeText) {
                sipBadgeText.textContent = 'En Línea';
                sipBadgeText.className = 'text-base font-extrabold text-emerald-600 mt-1 tracking-tight';
            }
            if (sipBadgeSub) {
                sipBadgeSub.textContent = 'Ext: ' + SIP_USER;
                sipBadgeSub.className = 'inline-block text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100';
            }
        });
    
        ua.on('registrationFailed', function(e) {
            if (statusLabel) statusLabel.textContent = 'Sin conexión';
            if (statusDot) statusDot.className = 'w-2 h-2 bg-rose-400 rounded-full inline-block';
            if (headerSipDot) headerSipDot.className = 'absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full';
            if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Error Credenciales SIP';
            if (sipBadgeText) {
                sipBadgeText.textContent = 'Sin Registro';
                sipBadgeText.className = 'text-base font-extrabold text-rose-600 mt-1 tracking-tight';
            }
            if (sipBadgeSub) {
                sipBadgeSub.textContent = 'Verificar Extensión';
                sipBadgeSub.className = 'inline-block text-[10px] text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100';
            }
        });
    
        ua.on('newRTCSession', function(data) {
            if (session && session !== data.session) {
                data.session.terminate();
                return;
            }
            
            session = data.session;

            if (session.direction === 'incoming') {
                resetBotones(true);
                if (statusLabel) statusLabel.textContent = 'Llamada entrante...';
                
                let callerId = session.remote_identity.uri.user;
                if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-emerald-600 animate-pulse"></i> Llamada de: ' + callerId;
                
                if (softphoneDrawer && softphoneDrawer.classList.contains('translate-x-full')) {
                    toggleSoftphone();
                }

                session.answer({
                    mediaConstraints: { audio: true, video: false },
                    rtcOfferConstraints: { offerToReceiveAudio: 1 }
                });
            }

            if (session.connection) {
                session.connection.addEventListener('track', (e) => {
                    if (e.track.kind === 'audio') {
                        const remoteAudio = document.getElementById("remoteAudio");
                        if (remoteAudio) {
                            remoteAudio.srcObject = e.streams[0];
                            remoteAudio.play().catch(err => console.log("Error reproduciendo audio remoto:", err));
                        }
                    }
                });
            }

            session.on('accepted', () => {
                if (statusLabel) statusLabel.textContent = 'En llamada';
                if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-emerald-600 animate-pulse"></i> En llamada activa';
            });

            session.on('ended', () => resetBotones(false));
            session.on('failed', () => resetBotones(false));
        });
    
        ua.start();
    }

        function makeCall() {
            if (!ua || !ua.isRegistered()) return alert("No registrado en el servidor SIP");
            if (!dialedNumber) return alert("Ingrese un número a discar");
            iniciarLlamada(dialedNumber);
        }

        function callQuick(area) {
            const ext = typeof quickMap !== 'undefined' ? quickMap[area] : area;
            if (!ext) return alert("Área no válida");
            dialedNumber = ext;
            if (display) display.value = ""; 
            makeCall();
        }

        function iniciarLlamada(dest) {
            const target = `sip:${dest}@${ua.configuration.uri.host}`;
            
            if (statusLabel) statusLabel.textContent = 'Marcando...';
            if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-amber-600 animate-pulse"></i> Marcando al ' + dest + '...';

            session = ua.call(target, {
                mediaConstraints: { audio: true, video: false },
                rtcOfferConstraints: { offerToReceiveAudio: 1 }
            });

            resetBotones(true);

            session.connection.addEventListener('track', (e) => {
                if (e.track.kind === 'audio') {
                    const remoteAudio = document.getElementById("remoteAudio");
                    if (remoteAudio) {
                        remoteAudio.srcObject = e.streams[0];
                        remoteAudio.play().catch(err => console.log(err));
                    }
                }
            });

            session.on('accepted', () => {
                if (statusLabel) statusLabel.textContent = 'En llamada';
                if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-emerald-600 animate-pulse"></i> En llamada activa';
            });

            session.on('ended', resetBotones);
            session.on('failed', resetBotones);
        }

        function hangUp() {
            if (session) {
                try { session.terminate(); } catch(e) {}
            }
            resetBotones(false);
            dialedNumber = '';
            if (display) display.value = '';
            if (statusLabel) statusLabel.textContent = 'En línea';
            if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-shield-halved text-[#00a884]"></i> Listo para discar';
            
            const remoteAudio = document.getElementById("remoteAudio");
            if (remoteAudio) {
                remoteAudio.pause();
                remoteAudio.srcObject = null;
            }
        }

        function resetBotones(inCall = false) {
            const btnLlamar = document.getElementById("btnLlamar");
            const btnColgar = document.getElementById("btnColgar");
            if (btnLlamar) btnLlamar.classList.toggle("hidden", inCall);
            if (btnColgar) btnColgar.classList.toggle("hidden", !inCall);
            if (!inCall) session = null;
        }

        function toggleMute() {
            if (!session) return;
            const btnMute = document.getElementById('btnMute');
            if (!isMuted) {
                session.mute({ audio: true, video: false });
                isMuted = true;
                if (btnMute) btnMute.className = 'w-9 h-9 rounded-full bg-rose-50 border border-rose-300 text-rose-600 flex items-center justify-center text-xs shadow-2xs transition-colors';
            } else {
                session.unmute({ audio: true, video: false });
                isMuted = false;
                if (btnMute) btnMute.className = 'w-9 h-9 rounded-full bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 flex items-center justify-center text-xs shadow-2xs transition-colors';
            }
        }

        function openTransferModal() {
            let targetExt = prompt("Ingrese la extensión o número al cual desea transferir la llamada:");
            if (targetExt && session) {
                session.refer('sip:' + targetExt + '@' + SIP_DOMAIN);
                alert("Transferencia enviada a la extensión: " + targetExt);
            }
        }

        function changeAgentStatus(status) {
            console.log("Estado del agente cambiado a:", status);
        }

        if (typeof JsSIP !== "undefined") {
            window.onload = connect;
        }
    </script>
</body>
</html>