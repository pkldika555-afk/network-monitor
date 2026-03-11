const prevStatus = {};
const mutedServices = new Set(); 

let alertCooldown = false;
const COOLDOWN_MS = 3000;

let audioCtx = null;
let alertBuffer = null;

async function loadSound() {
    if (alertBuffer) return; 
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const res      = await fetch('/sounds/danger_sms.mp3');
        const arrBuf   = await res.arrayBuffer();
        alertBuffer    = await audioCtx.decodeAudioData(arrBuf);
    } catch (e) {
        console.warn('Failed to load alert sound:', e);
    }
}

function playAlert() {
    if (alertCooldown) return;
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        if (!alertBuffer) { loadSound(); return; }

        const source = audioCtx.createBufferSource();
        source.buffer = alertBuffer;
        source.connect(audioCtx.destination);
        source.start(0);

        alertCooldown = true;
        setTimeout(() => { alertCooldown = false; }, COOLDOWN_MS);
    } catch (e) {
        console.warn('Play alert error:', e);
    }
}

window.trackStatusChange = function(id, newStatus) {
    const prev     = prevStatus[id];
    prevStatus[id] = newStatus;

    if (newStatus === 'offline' && prev !== 'offline' && prev !== undefined) {

        if (!mutedServices.has(String(id))) {
            playAlert();
        }
        return true;
    }
    if (newStatus === 'online' && mutedServices.has(String(id))) {
        mutedServices.delete(String(id));
        updateMuteBtn(id, false);
    }

    return false;
}
window.toggleMuteService = function(id) {
    const sid = String(id);
    if (mutedServices.has(sid)) {
        mutedServices.delete(sid);
        updateMuteBtn(id, false);
    } else {
        mutedServices.add(sid);
        updateMuteBtn(id, true);
    }
}

function updateMuteBtn(id, isMuted) {
    const btn  = document.getElementById(`mute-svc-${id}`);
    const icon = document.getElementById(`mute-icon-${id}`);
    if (!btn || !icon) return;
    if (isMuted) {
        btn.classList.add('text-red-400', 'border-red-800/50', 'bg-red-900/20');
        btn.classList.remove('text-gray-500', 'border-gray-700');
        icon.textContent = '🔇';
        btn.title = 'Muted — klik untuk unmute';
    } else {
        btn.classList.remove('text-red-400', 'border-red-800/50', 'bg-red-900/20');
        btn.classList.add('text-gray-500', 'border-gray-700');
        icon.textContent = '🔔';
        btn.title = 'Klik untuk mute service ini';
    }
}

window.initPrevStatus = function() {
    document.querySelectorAll('.service-card').forEach(card => {
        const id     = card.id.replace('card-', '');
        const status = card.dataset.status;
        if (id && status) prevStatus[id] = status;
    });
    loadSound(); // preload MP3
}

document.addEventListener('DOMContentLoaded', window.initPrevStatus);