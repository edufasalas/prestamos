<!-- Botón Flotante del Widget con Branding Profesional -->
<div class="fixed bottom-6 right-6 z-50">
    <button id="openChatBtn" onclick="toggleChatModal()" class="bg-slate-900 hover:bg-slate-800 text-white p-3.5 rounded-full shadow-2xl flex items-center justify-center transition-all duration-200 hover:scale-105 border border-slate-700 group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400 group-hover:text-indigo-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>
</div>

<!-- Ventana Modal de Chat Corporativa Avanzada -->
<div id="chatModal" class="hidden fixed bottom-20 right-6 w-[90vw] max-w-[820px] h-[80vh] max-h-[620px] bg-white rounded-xl shadow-2xl border border-slate-200 z-50 flex overflow-hidden font-sans">
    
    <!-- Sidebar de Contactos Corporativo -->
    <div class="w-1/3 border-r border-slate-200 bg-slate-50 flex flex-col h-full">
        <div class="p-4 bg-slate-900 text-white font-semibold text-xs uppercase tracking-wider flex justify-between items-center border-b border-slate-800">
            <span>Directorio Interno</span>
            <span class="bg-indigo-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">SIP VoIP</span>
        </div>
        <div id="contactList" class="flex-1 overflow-y-auto divide-y divide-slate-100">
            <div class="p-4 text-xs text-slate-400 text-center">Cargando directorio...</div>
        </div>
    </div>

    <!-- Panel Principal de Conversación -->
    <div class="w-2/3 flex flex-col bg-white h-full">
        <!-- Cabecera Ejecutiva -->
        <div class="px-4 py-3 bg-white border-b border-slate-200 flex justify-between items-center shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <h3 id="activeChatTitle" class="font-bold text-xs text-slate-800 tracking-wide uppercase">Selecciona un contacto</h3>
            </div>
            <button onclick="toggleChatModal()" class="text-slate-400 hover:text-slate-700 text-xl font-semibold transition-colors">&times;</button>
        </div>

        <!-- Contenedor de Mensajes -->
        <div id="chatMessages" class="flex-1 p-5 overflow-y-auto space-y-4 bg-slate-50/50 flex flex-col">
            <div class="text-center text-slate-400 my-auto text-xs font-medium">Selecciona un agente del directorio para iniciar la comunicación segura</div>
        </div>

        <!-- Área de Redacción Corporativa con Archivos y Audios -->
        <div id="chatInputArea" class="p-3 bg-white border-t border-slate-200 hidden flex flex-col space-y-2">
            <!-- Barra de vista previa de archivos seleccionados -->
            <div id="filePreviewContainer" class="hidden items-center justify-between px-3 py-1.5 bg-slate-100 rounded-lg text-xs text-slate-700">
                <span id="fileNameDisplay" class="truncate font-medium"></span>
                <button type="button" onclick="clearFile()" class="text-red-500 hover:text-red-700 font-bold ml-2">&times;</button>
            </div>

            <form id="chatForm" onsubmit="sendMessage(event)" class="flex items-center space-x-2">
                <!-- Botón oculto para adjuntar archivos -->
                <input type="file" id="mediaFile" class="hidden" onchange="handleFileSelect(event)">
                <button type="button" onclick="document.getElementById('mediaFile').click()" title="Adjuntar Archivo" class="p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </button>

                <!-- Botón para grabar Nota de Voz -->
                <button type="button" id="recordAudioBtn" onclick="toggleAudioRecording()" title="Grabar Nota de Voz" class="p-2.5 text-slate-500 hover:text-red-600 hover:bg-slate-100 rounded-lg transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </button>

                <!-- Campo de Texto -->
                <input type="text" id="chatBody" placeholder="Escribe un mensaje corporativo..." class="flex-1 bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-xs text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition-all" autocomplete="off">
                
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-semibold tracking-wide transition-all shadow-sm">Enviar</button>
            </form>
        </div>
    </div>
</div>

<!-- Script con Rutas VoIP Originales y Sonido de Alerta Activo -->
<script>
let chatOpen = false;
let activeExtension = null;
let pollingInterval = null;
let contactsLoaded = false;
let selectedFile = null;

let mediaRecorder;
let audioChunks = [];
let isRecording = false;
window.audioIsPlaying = false;

// Control de seguridad para liberar el bloqueo de audio tras 15 segundos máximos por si acaso
let audioLockTimeout = null;

let audioCtx = null;
document.addEventListener('click', () => {
    if (!audioCtx) {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (AudioContext) audioCtx = new AudioContext();
    }
}, { once: true });

function toggleChatModal() {
    const modal = document.getElementById('chatModal');
    chatOpen = !chatOpen;
    
    const chatBtn = document.getElementById('openChatBtn');

    if (chatOpen) {
        modal.classList.remove('hidden');
        if (!contactsLoaded) loadContacts();
        
        // Apagar la alerta roja al abrir
        if (chatBtn) {
            chatBtn.classList.remove('bg-red-600', 'animate-bounce', 'ring-4', 'ring-red-400', 'ring-red-300');
            chatBtn.classList.add('bg-slate-900', 'hover:bg-slate-800');
        }
        window.lastUnreadState = false;
        window.blockAlertsUntil = Date.now() + 15000; // Bloquea alertas al abrir

        if (activeExtension) {
            loadChatHistory();
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(loadChatHistory, 3000);
        }
    } else {
        modal.classList.add('hidden');
        clearInterval(pollingInterval);
        pollingInterval = null;

        // AQUÍ ESTABA EL ERROR: Al cerrar, apagamos el botón y bloqueamos por 15 segundos para que no salte solo
        window.blockAlertsUntil = Date.now() + 15000; 
        window.lastUnreadState = false;

        if (chatBtn) {
            chatBtn.classList.remove('bg-red-600', 'animate-bounce', 'ring-4', 'ring-red-400', 'ring-red-300');
            chatBtn.classList.add('bg-slate-900', 'hover:bg-slate-800');
        }
    }
}

function loadContacts() {
    fetch('/voip/chat/contacts')
        .then(res => res.json())
        .then(contacts => {
            const listContainer = document.getElementById('contactList');
            listContainer.innerHTML = '';
            
            if (!Array.isArray(contacts) || contacts.length === 0) {
                listContainer.innerHTML = '<div class="p-4 text-xs text-slate-400 text-center">No hay contactos disponibles</div>';
                return;
            }

            contacts.forEach(c => {
                const initials = c.agent_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                listContainer.innerHTML += `
                    <div id="contact-item-${c.extension_number}" onclick="selectContact('${c.extension_number}', '${c.agent_name}')" class="p-3.5 hover:bg-slate-100/80 cursor-pointer transition-all flex items-center space-x-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center border border-indigo-100 shadow-2xs">
                            ${initials}
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="font-semibold text-xs text-slate-800 truncate">${c.agent_name}</span>
                            <span class="text-[10px] text-slate-500 font-medium">Ext: ${c.extension_number}</span>
                        </div>
                    </div>
                `;
            });
            contactsLoaded = true;
        }).catch(err => console.error("Error cargando contactos:", err));
}

function selectContact(ext, name) {
    activeExtension = ext;
    document.getElementById('activeChatTitle').innerText = `${name} — Ext. ${ext}`;
    document.getElementById('chatInputArea').classList.remove('hidden');

    // APAGAR EL BOTÓN ROJO Y BLOQUEAR ALERTAS POR 15 SEGUNDOS PARA QUE NO JODA
    const chatBtn = document.getElementById('openChatBtn');
    if (chatBtn) {
        chatBtn.classList.remove('bg-red-600', 'animate-bounce', 'ring-4', 'ring-red-400');
        chatBtn.classList.add('bg-slate-900', 'hover:bg-slate-800');
    }
    window.lastUnreadState = false;
    window.blockAlertsUntil = Date.now() + 15000; // Bloquea que se vuelva a poner rojo por 15 segundos

    // Cargar historial
    if (pollingInterval) clearInterval(pollingInterval);
    loadChatHistory();
    pollingInterval = setInterval(loadChatHistory, 3000);
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        selectedFile = file;
        document.getElementById('fileNameDisplay').innerText = `📎 ${file.name}`;
        document.getElementById('filePreviewContainer').classList.remove('hidden');
        document.getElementById('filePreviewContainer').classList.add('flex');
    }
}

function clearFile() {
    selectedFile = null;
    document.getElementById('mediaFile').value = '';
    document.getElementById('filePreviewContainer').classList.remove('flex');
    document.getElementById('filePreviewContainer').classList.add('hidden');
}

async function toggleAudioRecording() {
    const btn = document.getElementById('recordAudioBtn');
    const previewContainer = document.getElementById('filePreviewContainer');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    
    if (!isRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };

            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                selectedFile = new File([audioBlob], `voice_note_${Date.now()}.webm`, { type: 'audio/webm' });
                
                fileNameDisplay.innerText = `🎤 Nota de voz grabada y lista para enviar`;
                previewContainer.classList.remove('hidden');
                previewContainer.classList.add('flex');
            };

            mediaRecorder.start();
            isRecording = true;
            
            btn.classList.add('text-red-600', 'bg-red-50', 'animate-pulse');
            fileNameDisplay.innerText = `🔴 Grabando... (Haz clic otra vez en el micrófono para parar)`;
            previewContainer.classList.remove('hidden');
            previewContainer.classList.add('flex');

        } catch (err) {
            alert("No se pudo acceder al micrófono.");
            console.error(err);
        }
    } else {
        mediaRecorder.stop();
        isRecording = false;
        btn.classList.remove('text-red-600', 'bg-red-50', 'animate-pulse');
    }
}

function handleAudioPlay() {
    window.audioIsPlaying = true;
    // Timeout de seguridad: si pasa más de 60s reproduciendo o se queda colgado, libera el bloqueo
    if (audioLockTimeout) clearTimeout(audioLockTimeout);
    audioLockTimeout = setTimeout(() => {
        window.audioIsPlaying = false;
    }, 60000);
}

function handleAudioEndOrPause() {
    window.audioIsPlaying = false;
    if (audioLockTimeout) clearTimeout(audioLockTimeout);
}

function loadChatHistory() {
    if (!activeExtension) return;
    if (window.audioIsPlaying) return; // Pausa el refresco solo mientras el usuario escucha un audio de forma activa

    fetch(`/voip/chat/history/${activeExtension}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('chatMessages');
            const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;
            
            container.innerHTML = '';
            
            if(!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div class="text-center text-slate-400 my-auto text-xs font-medium">No hay registros de mensajes previos con este contacto</div>';
                return;
            }

            data.forEach(msg => {
                const isOutgoing = msg.direction === 'outgoing';
                const alignClass = isOutgoing ? 'items-end' : 'items-start';
                const bubbleBg = isOutgoing ? 'bg-slate-900 text-white rounded-br-xs shadow-sm' : 'bg-white text-slate-800 border border-slate-200 rounded-bl-xs shadow-2xs';
                const headerColor = isOutgoing ? 'text-slate-300 bg-slate-800/80' : 'text-indigo-700 bg-indigo-50';
                const senderLabel = isOutgoing ? 'Saliente' : `Recibido: ${msg.from_contact}`;
                
                let contentHtml = `<p class="text-xs leading-relaxed break-words font-normal">${msg.body || ''}</p>`;
                
                const bodyStr = msg.body || '';
                const isAudio = bodyStr.includes('.webm') || 
                                bodyStr.includes('.mp3') || 
                                bodyStr.includes('.wav') || 
                                bodyStr.includes('voice_note');

                if (isAudio) {
                    contentHtml = `
                        <audio controls 
                            onplay="handleAudioPlay()" 
                            onpause="handleAudioEndOrPause()" 
                            onended="handleAudioEndOrPause()" 
                            class="w-48 h-8 mt-1">
                            <source src="${bodyStr}" type="audio/webm">
                            <source src="${bodyStr}" type="audio/mp3">
                            Tu navegador no soporta elementos de audio.
                        </audio>
                    `;
                } else if (bodyStr.includes('/storage/') || bodyStr.includes('/uploads/') || bodyStr.startsWith('http')) {
                    contentHtml = `<a href="${bodyStr}" target="_blank" class="text-xs underline text-indigo-400 font-semibold flex items-center space-x-1"><span>📥 Descargar Archivo Adjunto</span></a>`;
                }

                container.innerHTML += `
                    <div class="flex flex-col ${alignClass} w-full my-1">
                        <div class="max-w-[80%] p-3.5 rounded-xl ${bubbleBg}">
                            <div class="text-[9px] font-bold px-1.5 py-0.5 rounded mb-1.5 inline-block tracking-wide uppercase ${headerColor}">
                                ${senderLabel}
                            </div>
                            ${contentHtml}
                            <span class="block text-[9px] opacity-60 mt-1.5 text-right font-mono">${msg.created_at}</span>
                        </div>
                    </div>
                `;
            });

            if (isScrolledToBottom) container.scrollTop = container.scrollHeight;
        }).catch(err => console.error("Error sincronizando chat:", err));
}

function sendMessage(e) {
    e.preventDefault();
    if (!activeExtension) return;

    const bodyInput = document.getElementById('chatBody');
    const textVal = bodyInput.value.trim();

    const formData = new FormData();
    formData.append('to_contact', activeExtension);
    formData.append('body', textVal);
    if (selectedFile) {
        formData.append('file', selectedFile);
    }

    fetch('/voip/chat/send', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            bodyInput.value = '';
            clearFile();
            loadChatHistory();
        }
    }).catch(err => console.error("Error enviando mensaje multimedia:", err));
}

function playNotificationSound() {
    try {
        if (!audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) audioCtx = new AudioContext();
        }
        if (audioCtx) {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(659.25, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.25);
        }
    } catch (e) {
        console.warn("Audio bloqueado por política del navegador.");
    }
}


// Variable global para evitar que el sonido se repita en bucle
window.lastUnreadState = false;
window.lastCheckedMaxId = 0; // Guardará el último ID conocido

setInterval(() => {
    // Si el chat está abierto O estamos dentro del tiempo de bloqueo, no hace nada
    if (chatOpen || (window.blockAlertsUntil && Date.now() < window.blockAlertsUntil)) {
        return; 
    }

    fetch('/voip/chat/check-unread')
        .then(res => res.json())
        .then(data => {
            const chatBtn = document.getElementById('openChatBtn');
            const hasNewMessages = data.has_new === true || data.has_new == 1;

            if (hasNewMessages) {
                if (!window.lastUnreadState) {
                    playNotificationSound();
                }
                window.lastUnreadState = true;

                if (chatBtn) {
                    chatBtn.classList.remove('bg-slate-900', 'hover:bg-slate-800');
                    chatBtn.classList.add('bg-red-600', 'animate-bounce', 'ring-4', 'ring-red-400');
                }
            } else {
                window.lastUnreadState = false;
                if (chatBtn) {
                    chatBtn.classList.remove('bg-red-600', 'animate-bounce', 'ring-4', 'ring-red-400');
                    chatBtn.classList.add('bg-slate-900', 'hover:bg-slate-800');
                }
            }
        }).catch(err => {});
}, 4000);


</script>
