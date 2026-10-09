// public/js/softphone-engine.js
let ua, session = null, dialedNumber = '', isMuted = false, sharedAudioCtx = null;

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
    const softphoneDrawer = document.getElementById('softphone-drawer');
    if (softphoneDrawer) softphoneDrawer.classList.toggle('translate-x-full');
}

function playDtmfTone(digit) {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!sharedAudioCtx) sharedAudioCtx = new AudioContext();
        if (sharedAudioCtx.state === 'suspended') sharedAudioCtx.resume();
        
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
        
        osc1.type = 'sine'; osc1.frequency.setValueAtTime(lowFreq, sharedAudioCtx.currentTime);
        osc2.type = 'sine'; osc2.frequency.setValueAtTime(highFreq, sharedAudioCtx.currentTime);
        
        const startTime = sharedAudioCtx.currentTime;
        const duration = 0.15;
        
        gainNode.gain.setValueAtTime(0.001, startTime);
        gainNode.gain.linearRampToValueAtTime(0.1, startTime + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
        
        osc1.connect(gainNode); osc2.connect(gainNode);
        gainNode.connect(sharedAudioCtx.destination);
        
        osc1.start(startTime); osc2.start(startTime);
        osc1.stop(startTime + duration); osc2.stop(startTime + duration);
    } catch(e) {
        console.error("Error reproduciendo tono DTMF:", e);
    }
}

function appendDigit(d) {
    playDtmfTone(d);
    dialedNumber += d;
    const display = document.getElementById('display');
    if (display) display.value = dialedNumber;
    if (session && session.isEstablished()) {
        session.sendDTMF(d);
    }
}

function deleteLastDigit() {
    dialedNumber = dialedNumber.slice(0, -1);
    const display = document.getElementById('display');
    if (display) display.value = dialedNumber;
}

document.addEventListener('keydown', function(e) {
    const softphoneDrawer = document.getElementById('softphone-drawer');
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

function initSoftphone(sipUser, sipPass, sipWs, sipDomain) {
    if (typeof JsSIP === 'undefined') return;
    const socket = new JsSIP.WebSocketInterface(sipWs);
    const config = {
        sockets: [socket],
        uri: "sip:" + sipUser + "@" + sipDomain,
        password: sipPass,
        session_timers: false
    };
    ua = new JsSIP.UA(config);

    const statusLabel = document.getElementById('sip-status');
    const statusDot = document.getElementById('status-dot');
    const headerSipDot = document.getElementById('header-sip-dot');
    const callDurationLabel = document.getElementById('call-duration');
    const sipBadgeText = document.getElementById('sip-badge-text');
    const sipBadgeSub = document.getElementById('sip-badge-sub');

    ua.on('registered', function(e) {
        if (statusLabel) statusLabel.textContent = 'En línea';
        if (statusDot) statusDot.className = 'w-2 h-2 bg-emerald-400 rounded-full inline-block';
        if (headerSipDot) headerSipDot.className = 'absolute -top-1 -right-1 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full';
        if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-circle-check text-[#00a884]"></i> Conectado a ' + sipDomain;
        if (sipBadgeText) {
            sipBadgeText.textContent = 'En Línea';
            sipBadgeText.className = 'text-base font-extrabold text-emerald-600 mt-1 tracking-tight';
        }
        if (sipBadgeSub) {
            sipBadgeSub.textContent = 'Ext: ' + sipUser;
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
        session = data.session;
        resetBotones(true);

        if (session.direction === 'incoming') {
            if (statusLabel) statusLabel.textContent = 'Llamada entrante...';
            if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-emerald-600 animate-pulse"></i> Llamada entrante de: ' + session.remote_identity.uri.user;
        }

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
    });

    ua.start();
}

function connect() {
            if (typeof JsSIP === 'undefined') return;
            const socket = new JsSIP.WebSocketInterface(SIP_WS);
            const config = {
                sockets: [socket],
                uri: "sip:" + SIP_USER + "@" + SIP_DOMAIN,
                password: SIP_PASS,
                session_timers: false
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
                session = data.session;
                resetBotones(true);

                if (session.direction === 'incoming') {
                    if (statusLabel) statusLabel.textContent = 'Llamada entrante...';
                    if (callDurationLabel) callDurationLabel.innerHTML = '<i class="fa-solid fa-phone-volume text-emerald-600 animate-pulse"></i> Llamada entrante de: ' + session.remote_identity.uri.user;
                }

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
            });

            ua.start();
        }

function makeCall() {
    if (!ua || !ua.isRegistered()) return alert("No registrado en el servidor SIP");
    const display = document.getElementById('display');
    dialedNumber = display ? display.value : dialedNumber;
    if (!dialedNumber) return alert("Ingrese un número a discar");
    iniciarLlamada(dialedNumber);
}

function callQuick(area) {
    const ext = typeof quickMap !== 'undefined' ? quickMap[area] : area;
    if (!ext) return alert("Área no válida");
    dialedNumber = ext;
    const display = document.getElementById('display');
    if (display) display.value = ""; 
    makeCall();
}

function iniciarLlamada(dest) {
    const target = `sip:${dest}@${ua.configuration.uri.host}`;
    const statusLabel = document.getElementById('sip-status');
    const callDurationLabel = document.getElementById('call-duration');
    
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
    const display = document.getElementById('display');
    if (display) display.value = '';
    const statusLabel = document.getElementById('sip-status');
    if (statusLabel) statusLabel.textContent = 'En línea';
    const callDurationLabel = document.getElementById('call-duration');
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

function openTransferModal() {
    let targetExt = prompt("Ingrese la extensión o número al cual desea transferir la llamada:");
    if (targetExt && session) {
        session.refer('sip:' + targetExt + '@' + ua.configuration.uri.host);
        alert("Transferencia enviada a la extensión: " + targetExt);
    }
}

function changeAgentStatus(status) {
    console.log("Estado del agente cambiado a:", status);
}
if (typeof JsSIP !== "undefined") {
    window.onload = connect;
}