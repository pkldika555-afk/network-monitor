window.prevStatus = {};
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
        console.log("🤫 Global Snooze aktif, alarm ditahan.");
        return;
    }
    if (!audioCtx)
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === "suspended") {
        audioCtx.resume().then(() => {
            if (audioCtx.state === "running") playLoop();
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

function updatePulse(id, isAlarming) {
    const card = document.getElementById(`card-${id}`);
    if (!card) return;
    card.classList.toggle("alarm-pulse", isAlarming);
}

window.anyStillAlarming = function () {
    const now = Date.now();
    return [...document.querySelectorAll(".service-card")].some((card) => {
        const sid = String(card.dataset.id || card.id.replace("card-", ""));
        const status = card.dataset.status;
        const expiry = window.mutedServices[sid];
        const isMuted = expiry && expiry > now;
        return status === "offline" && !isMuted;
    });
};

window.startAlarm = startAlarm;
window.stopAlarm = stopAlarm;

window.trackStatusChange = function (id, newStatus) {
    const sid = String(id);
    const prev = window.prevStatus[sid];
    window.prevStatus[sid] = newStatus;

    const card = document.getElementById(`card-${sid}`);
    if (card) card.dataset.status = newStatus;

    if (newStatus === "offline" && prev !== "offline" && prev !== undefined) {
        if (Date.now() > alert_until) {
            const expiry = window.mutedServices[sid];
            const isMuted = expiry && expiry > Date.now();
            if (!isMuted) {
                console.log(`🚨 Alarm triggered: sid=${sid}`);
                startAlarm();
                updatePulse(id, true);
            } else {
                console.log(`🔇 sid=${sid} offline tapi di-mute, skip alarm`);
            }
        }
    }

    if (newStatus === "online" && prev === "offline") {
        delete window.mutedServices[sid];
        window.saveMuteToStorage();
        window.updateMuteUI(id, false);
        updatePulse(id, false);

        if (!window.anyStillAlarming()) {
            console.log("✅ Semua service sehat. Alarm dimatikan.");
            stopAlarm(true);
        }
    }
    return false;
};

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
        if (window.anyStillAlarming()) startAlarm();
    };
    document.body.appendChild(banner);
}

window.saveCooldown = function () {
    const input = document.getElementById("cooldown-input");
    const unit = document.getElementById("cooldown-unit");
    const val = parseInt(input.value);

    if (isNaN(val) || val < 1) {
        input.classList.add("border-red-500");
        setTimeout(() => input.classList.remove("border-red-500"), 1500);
        return;
    }

    const multiplier = { s: 1, m: 60, h: 3600 };
    COOLDOWN_MS = val * multiplier[unit.value] * 1000;

    alert_until = Date.now() + COOLDOWN_MS;
    localStorage.setItem("alert_until_timestamp", String(alert_until));
    localStorage.setItem("alert_cooldown_val", String(val));
    localStorage.setItem("alert_cooldown_unit", unit.value);

    stopAlarm(true);

    const label = document.getElementById("cooldown-label");
    if (label) {
        label.classList.remove("text-gray-600");
        label.classList.add("text-yellow-500", "font-bold");
        label.textContent = "Snooze Aktif...";
    }
};

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
                label.classList.add("text-yellow-500", "font-bold");
                label.textContent = "Snooze Aktif...";
            } else {
                label.textContent = `Saat ini: ${savedVal} ${labelText[savedUnit]}`;
            }
        }
    }
}

window.cancelCooldown = function () {
    alert_until = 0;
    localStorage.removeItem("alert_until_timestamp");
    const label = document.getElementById("cooldown-label");
    if (label) {
        label.textContent = "Monitoring Aktif";
        label.classList.remove("text-yellow-500", "font-bold");
        label.classList.add("text-gray-600");
    }
    if (window.anyStillAlarming()) {
        console.log("📢 Masih ada service offline, alarm dinyalakan kembali.");
        startAlarm();
    }
};

window.initPrevStatus = function () {
    document.querySelectorAll(".service-card").forEach((card) => {
        const id = card.id.replace("card-", "");
        const status = card.dataset.status;
        if (id && status) window.prevStatus[String(id)] = status;
    });

    const savedUntil = localStorage.getItem("alert_until_timestamp");
    if (savedUntil) alert_until = parseInt(savedUntil);

    restoreCooldown();

    loadSound().then(() => {
        if (audioCtx && audioCtx.state === "suspended") {
            showAudioBanner();
        }
        if (window.anyStillAlarming() && Date.now() > alert_until) {
            startAlarm();
            document.querySelectorAll(".service-card").forEach((card) => {
                const sid = String(card.dataset.id || card.id.replace("card-", ""));
                const expiry = window.mutedServices[sid];
                const isMuted = expiry && expiry > Date.now();
                if (card.dataset.status === "offline" && !isMuted) {
                    updatePulse(sid, true);
                }
            });
        } else if (window.anyStillAlarming() && Date.now() <= alert_until) {
            console.log("🔕 Offline terdeteksi, ditahan Global Snooze.");
        }
    });
};

document.addEventListener("DOMContentLoaded", window.initPrevStatus);

setInterval(() => {
    const now = Date.now();
    let anyExpired = false;

    for (const sid in window.mutedServices) {
        const expiry = window.mutedServices[sid];
        const remaining = expiry - now;
        const label = document.getElementById(`timer-label-${sid}`);

        if (remaining > 0) {
            if (label) {
                label.classList.remove("hidden");
                if (remaining >= 3600000) {
                    label.textContent = Math.ceil(remaining / 3600000) + "h";
                } else if (remaining >= 60000) {
                    label.textContent = Math.ceil(remaining / 60000) + "m";
                } else {
                    label.textContent = Math.ceil(remaining / 1000) + "s";
                }
            }
        } else {
            console.log(`🔥 SNOOZE EXPIRED: ID ${sid} kembali dipantau`);
            delete window.mutedServices[sid];
            window.saveMuteToStorage();
            window.updateMuteUI(sid, false);
            updatePulse(sid, false);
            anyExpired = true;
        }
    }

    if (anyExpired && window.anyStillAlarming() && Date.now() > alert_until) {
        startAlarm();
    }

    const globalLabel = document.getElementById("cooldown-label");
    if (globalLabel && alert_until > now) {
        const rem = alert_until - now;
        let timeStr;
        if (rem >= 3600000) {
            timeStr = Math.ceil(rem / 3600000) + " jam";
        } else if (rem >= 60000) {
            timeStr = Math.ceil(rem / 60000) + " menit";
        } else {
            timeStr = Math.ceil(rem / 1000) + " detik";
        }
        globalLabel.textContent = `⏳ Snooze: ${timeStr} lagi`;
        globalLabel.classList.add("text-yellow-500", "font-bold");
        globalLabel.classList.remove("text-gray-600");
    } else if (globalLabel && alert_until > 0 && alert_until <= now) {
        alert_until = 0;
        localStorage.removeItem("alert_until_timestamp");
        globalLabel.textContent = "Monitoring Aktif";
        globalLabel.classList.remove("text-yellow-500", "font-bold");
        globalLabel.classList.add("text-gray-600");
        if (window.anyStillAlarming()) startAlarm();
    }

}, 1000);