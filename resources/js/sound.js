const prevStatus = {};
let isMuted = false;
let alertCooldown = false;
const COOLDOWN_MS = 3000;

let audioCtx = null;

function createAlertSound() {
    try {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') audioCtx.resume();

        const playTone = (freq, startTime, duration) => {
            const osc  = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);
            gain.gain.setValueAtTime(0.4, startTime);
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
            osc.start(startTime);
            osc.stop(startTime + duration);
        };

        playTone(880, audioCtx.currentTime, 0.3);
        playTone(660, audioCtx.currentTime + 0.35, 0.3);
    } catch (e) {
        console.warn('Web Audio API error:', e);
    }
}

function playAlert() {
    if (isMuted || alertCooldown) return;
    createAlertSound();
    alertCooldown = true;
    setTimeout(() => { alertCooldown = false; }, COOLDOWN_MS);
}

window.trackStatusChange = function(id, newStatus) {
    const prev     = prevStatus[id];
    prevStatus[id] = newStatus;
    if (newStatus === 'offline' && prev !== 'offline' && prev !== undefined) {
        playAlert();
        return true;
    }
    return false;
}

window.initPrevStatus = function() {
    document.querySelectorAll('.service-card').forEach(card => {
        const id     = card.id.replace('card-', '');
        const status = card.dataset.status;
        if (id && status) prevStatus[id] = status;
    });
}

window.toggleMute = function() {
    isMuted = !isMuted;
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const btn  = document.getElementById('mute-btn');
    const icon = document.getElementById('mute-icon');
    if (!btn || !icon) return;
    if (isMuted) {
        btn.classList.add('text-red-400', 'border-red-800/50', 'bg-red-900/20');
        btn.classList.remove('text-gray-500', 'border-gray-700');
        icon.textContent = '🔇';
    } else {
        btn.classList.remove('text-red-400', 'border-red-800/50', 'bg-red-900/20');
        btn.classList.add('text-gray-500', 'border-gray-700');
        icon.textContent = '🔔';
        createAlertSound(); 
    }
}

document.addEventListener('DOMContentLoaded', window.initPrevStatus);