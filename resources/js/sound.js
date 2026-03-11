const mutedServices = new Set(); 

let COOLDOWN_MS  = 3000;
let audioCtx     = null;
let alertBuffer  = null;
let activeSource = null;

async function loadSound() {
    if (alertBuffer) return;
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const res    = await fetch('/sounds/danger_sms.mp3');
        const arrBuf = await res.arrayBuffer();
        alertBuffer  = await audioCtx.decodeAudioData(arrBuf);
    } catch (e) {
        console.warn('Failed to load alert sound:', e);
    }
}

function startAlarm() {
    if (activeSource) return;
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        if (!alertBuffer) { loadSound(); return; }

        const source  = audioCtx.createBufferSource();
        source.buffer = alertBuffer;
        source.loop   = true;
        source.connect(audioCtx.destination);
        source.start(0);
        activeSource = source;
    } catch (e) {
        console.warn('startAlarm error:', e);
    }
}

function stopAlarm() {
    if (!activeSource) return;
    try { activeSource.stop(); } catch (e) {}
    activeSource = null;
}

function updatePulse(id, isAlarming) {
    const card = document.getElementById(`card-${id}`);
    if (!card) return;
    if (isAlarming) {
        card.classList.add('alarm-pulse');
    } else {
        card.classList.remove('alarm-pulse');
    }
}

function anyStillAlarming() {
    return [...document.querySelectorAll('.service-card')]
        .some(c => {
            const cardId = c.id.replace('card-', '');
            return c.dataset.status === 'offline' && !mutedServices.has(cardId);
        });
}

window.trackStatusChange = function(id, newStatus) {
    const sid  = String(id); 
    const prev = prevStatus[sid];
    prevStatus[sid] = newStatus;

    if (newStatus === 'offline' && prev !== 'offline' && prev !== undefined) {
        if (!mutedServices.has(sid)) {
            startAlarm();
            updatePulse(id, true);
        }
    }

    if (newStatus === 'online' && prev === 'offline') {
        mutedServices.delete(sid);
        updateMuteBtn(id, false);
        updatePulse(id, false);

        if (!anyStillAlarming()) stopAlarm();
    }

    return false;
}

window.toggleMuteService = function(id) {
    const sid = String(id);
    if (mutedServices.has(sid)) {
        mutedServices.delete(sid);
        updateMuteBtn(id, false);
        const card = document.getElementById(`card-${id}`);
        if (card && card.dataset.status === 'offline') {
            startAlarm();
            updatePulse(id, true);
        }
    } else {
        mutedServices.add(sid);
        updateMuteBtn(id, true);
        updatePulse(id, false);
        if (!anyStillAlarming()) stopAlarm();
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

window.saveCooldown = function() {
    const input = document.getElementById('cooldown-input');
    const unit  = document.getElementById('cooldown-unit');
    const val   = parseInt(input.value);

    if (isNaN(val) || val < 1) {
        input.classList.add('border-red-500');
        setTimeout(() => input.classList.remove('border-red-500'), 1500);
        return;
    }

    const multiplier = { s: 1, m: 60, h: 3600 };
    const labelText  = { s: 'detik', m: 'menit', h: 'jam' };
    COOLDOWN_MS = val * multiplier[unit.value] * 1000;

    localStorage.setItem('alert_cooldown_val', val);
    localStorage.setItem('alert_cooldown_unit', unit.value);

    const label = document.getElementById('cooldown-label');
    if (label) label.textContent = `Saat ini: ${val} ${labelText[unit.value]}`;
    if (typeof toast === 'function') toast(`Cooldown set: ${val} ${labelText[unit.value]}`, 'ok');
}

function restoreCooldown() {
    const savedVal  = localStorage.getItem('alert_cooldown_val');
    const savedUnit = localStorage.getItem('alert_cooldown_unit');
    if (!savedVal || !savedUnit) return;

    const multiplier = { s: 1, m: 60, h: 3600 };
    const labelText  = { s: 'detik', m: 'menit', h: 'jam' };
    COOLDOWN_MS = parseInt(savedVal) * multiplier[savedUnit] * 1000;

    const input = document.getElementById('cooldown-input');
    const unit  = document.getElementById('cooldown-unit');
    const label = document.getElementById('cooldown-label');
    if (input) input.value = savedVal;
    if (unit)  unit.value  = savedUnit;
    if (label) label.textContent = `Saat ini: ${savedVal} ${labelText[savedUnit]}`;
}

window.initPrevStatus = function() {
    document.querySelectorAll('.service-card').forEach(card => {
        const id     = card.id.replace('card-', '');
        const status = card.dataset.status;
        if (id && status) prevStatus[String(id)] = status; 
    });
    restoreCooldown();
    loadSound();
}

document.addEventListener('DOMContentLoaded', window.initPrevStatus);