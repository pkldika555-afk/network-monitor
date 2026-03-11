window.prevStatus = {};
window.mutedServices = new Set();
window.isUpdating = false;

let alertCooldown = false;
let COOLDOWN_MS = 3000;
let audioCtx = null;
let alertBuffer = null;
let activeSource = null;
let isLoadingSound = false;
let alert_until = 0;
async function loadSound() {
    if (alertBuffer || isLoadingSound) return;
    isLoadingSound = true;
    try {
        if (!audioCtx)
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const res = await fetch("/sounds/danger_sms.mp3");
        const arrBuf = await res.arrayBuffer();
        alertBuffer = await audioCtx.decodeAudioData(arrBuf);
        console.log("🔊 Sound system ready");
    } catch (e) {
        console.warn("Failed to load alert sound:", e);
    } finally {
        isLoadingSound = false;
    }
}

function playLoop() {
    if (!alertBuffer || activeSource) return;
    try {
        const source = audioCtx.createBufferSource();
        source.buffer = alertBuffer;
        source.loop = true;
        source.connect(audioCtx.destination);
        source.start(0);
        activeSource = source;
    } catch (e) {
        console.warn("playLoop error:", e);
    }
}

function startAlarm() {
    if (activeSource) return;

    if (Date.now() < alert_until) {
        console.log("🤫 Ssshhh... Sistem masih dalam masa Global Snooze.");
        return;
    }

    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') {
        audioCtx.resume().then(() => {
            if (audioCtx.state === 'running') playLoop();
        });
    } else {
        playLoop();
    }
}

function stopAlarm(isManual = false) {
    if (!activeSource) return;
    try {
        activeSource.stop();
        activeSource.disconnect();
    } catch (e) {}
    activeSource = null;

    if (!isManual) {
        const expiry = Date.now() + COOLDOWN_MS;
        localStorage.setItem("alert_cooldown_expiry", expiry);
        alertCooldown = true;

        setTimeout(() => {
            alertCooldown = false;
            localStorage.removeItem("alert_cooldown_expiry");
        }, COOLDOWN_MS);
    } else {
        alertCooldown = false;
        localStorage.removeItem("alert_cooldown_expiry");
    }
}

function checkExistingCooldown() {
    const expiry = localStorage.getItem("alert_cooldown_expiry");
    if (expiry) {
        const remaining = parseInt(expiry) - Date.now();
        if (remaining > 0) {
            alertCooldown = true;
            setTimeout(() => {
                alertCooldown = false;
            }, remaining);
        }
    }
}

function updatePulse(id, isAlarming) {
    const card = document.getElementById(`card-${id}`);
    if (!card) return;
    card.classList.toggle("alarm-pulse", isAlarming);
}

function anyStillAlarming() {
    return [...document.querySelectorAll(".service-card")].some((c) => {
        const cardId = c.id.replace("card-", "");
        return (
            c.dataset.status === "offline" && !window.mutedServices.has(cardId)
        );
    });
}

window.trackStatusChange = function(id, newStatus) {
    const sid = String(id);
    const prev = window.prevStatus[sid];
    window.prevStatus[sid] = newStatus;

    const card = document.getElementById(`card-${sid}`);
    if (card) {
        card.dataset.status = newStatus; 
    }

    if (newStatus === 'offline' && prev !== 'offline' && prev !== undefined) {
        if (Date.now() > alert_until) {
            if (!window.mutedServices.has(sid)) {
                startAlarm();
                updatePulse(id, true);
            }
        }
    }
    if (newStatus === 'online' && prev === 'offline') {
        window.mutedServices.delete(sid);
        updateMuteBtn(id, false);
        updatePulse(id, false);
        
        if (!anyStillAlarming()) {
            console.log("✅ Semua service sudah sehat. Mematikan alarm...");
            stopAlarm(true); 
        }
    }
    return false;
}

window.toggleMuteService = function(id) {
    const sid = String(id);
    
    if (window.mutedServices.has(sid)) {
        window.mutedServices.delete(sid);
        updateMuteBtn(id, false);
        
        alert_until = 0; 
        localStorage.removeItem('alert_until_timestamp');
        
        if (anyStillAlarming()) {
            startAlarm();
            updatePulse(id, true);
        }
    } else {
        window.mutedServices.add(sid);
        updateMuteBtn(id, true);
        updatePulse(id, false);
        
        alert_until = Date.now() + COOLDOWN_MS;
        localStorage.setItem('alert_until_timestamp', alert_until);
        
        console.log("🔇 Alarm di-Snooze sampai: " + new Date(alert_until).toLocaleTimeString());
        if (!anyStillAlarming() || alert_until > Date.now()) {
            stopAlarm(true); 
        }
    }
}

function updateMuteBtn(id, isMuted) {
    const btn = document.getElementById(`mute-svc-${id}`);
    const icon = document.getElementById(`mute-icon-${id}`);
    if (!btn || !icon) return;
    if (isMuted) {
        btn.classList.add("text-red-400", "border-red-800/50", "bg-red-900/20");
        btn.classList.remove("text-gray-500", "border-gray-700");
        icon.textContent = "🔇";
        btn.title = "Muted — klik untuk unmute";
    } else {
        btn.classList.remove(
            "text-red-400",
            "border-red-800/50",
            "bg-red-900/20",
        );
        btn.classList.add("text-gray-500", "border-gray-700");
        icon.textContent = "🔔";
        btn.title = "Klik untuk mute service ini";
    }
}

function showAudioBanner() {
    if (document.getElementById("audio-banner")) return;
    const banner = document.createElement("div");
    banner.id = "audio-banner";
    banner.className =
        "fixed top-14 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-4 py-2.5 bg-yellow-900/80 border border-yellow-700 rounded-lg text-xs mono text-yellow-300 shadow-xl cursor-pointer";
    banner.innerHTML =
        "⚠️ Audio dinonaktifkan browser — klik di sini untuk mengaktifkan alarm";
    banner.onclick = () => {
        if (audioCtx) audioCtx.resume();
        banner.remove();
        if (anyStillAlarming()) startAlarm();
    };
    document.body.appendChild(banner);
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
    COOLDOWN_MS = val * multiplier[unit.value] * 1000;

    alert_until = Date.now() + COOLDOWN_MS;
    localStorage.setItem('alert_until_timestamp', alert_until);

    stopAlarm(true); 

    const label = document.getElementById('cooldown-label');
    if (label) {
        label.classList.remove('text-gray-600');
        label.classList.add('text-yellow-500', 'font-bold');
        label.textContent = `Snooze Aktif...`;
    }
}
function restoreCooldown() {
    const savedVal = localStorage.getItem("alert_cooldown_val");
    const savedUnit = localStorage.getItem("alert_cooldown_unit");
    
    if (savedVal && savedUnit) {
        const multiplier = { s: 1, m: 60, h: 3600 };
        const labelText = { s: "detik", m: "menit", h: "jam" };
        COOLDOWN_MS = parseInt(savedVal) * multiplier[savedUnit] * 1000;

        const input = document.getElementById("cooldown-input");
        const unit = document.getElementById("cooldown-unit");
        const label = document.getElementById("cooldown-label");

        if (input) input.value = savedVal;
        if (unit) unit.value = savedUnit;
        
        if (label) {
            if (alert_until > Date.now()) {
                label.textContent = "Snooze Aktif..."; 
                label.classList.add('text-yellow-500');
            } else {
                label.textContent = `Saat ini: ${savedVal} ${labelText[savedUnit]}`;
            }
        }
    }
}

window.initPrevStatus = function () {
    document.querySelectorAll(".service-card").forEach((card) => {
        const id = card.id.replace("card-", "");
        const status = card.dataset.status;
        if (id && status) window.prevStatus[String(id)] = status;
    });

    const savedUntil = localStorage.getItem('alert_until_timestamp');
    if (savedUntil) {
        alert_until = parseInt(savedUntil);
    }

    restoreCooldown();

    loadSound().then(() => {
        if (audioCtx && audioCtx.state === "suspended") {
            showAudioBanner();
        }

        if (anyStillAlarming() && Date.now() > alert_until) {
            startAlarm();
            
            document.querySelectorAll(".service-card").forEach((card) => {
                const sid = card.id.replace("card-", "");
                if (
                    card.dataset.status === "offline" &&
                    !window.mutedServices.has(sid)
                ) {
                    updatePulse(sid, true);
                }
            });
        } else if (anyStillAlarming() && Date.now() <= alert_until) {
            console.log("🔕 Status offline terdeteksi, tapi alarm ditahan oleh Global Snooze.");
        }
    });
};
window.cancelCooldown = function() {
    alert_until = 0;
    localStorage.removeItem('alert_until_timestamp');
    const label = document.getElementById('cooldown-label');
    if (label) {
        label.textContent = "Monitoring Aktif";
        label.classList.remove('text-yellow-500', 'font-bold');
    }
    if (anyStillAlarming()) {
        console.log("📢 Masih ada service offline, alarm dinyalakan kembali.");
        startAlarm();
    }
}
document.addEventListener("DOMContentLoaded", window.initPrevStatus);
setInterval(() => {
    const label = document.getElementById('cooldown-label');
    if (!label || alert_until === 0) return;

    const remaining = alert_until - Date.now();

    if (remaining > 0) {
        const h = Math.floor(remaining / 3600000);
        const m = Math.floor((remaining % 3600000) / 60000);
        const s = Math.floor((remaining % 60000) / 1000);

        let timeStr = "";
        if (h > 0) timeStr += `${h}j `;
        if (m > 0 || h > 0) timeStr += `${m}m `;
        timeStr += `${s}s`;

        label.textContent = `Snooze: ${timeStr} lagi`;
        label.classList.add('text-yellow-500');
    } else {
        console.log("⏰ Waktu Snooze habis, membangunkan alarm...");
        
        window.cancelCooldown(); 
    }
}, 1000);