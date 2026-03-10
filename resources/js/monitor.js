// Ambil CSRF dari meta tag
const getCSRF = () => document.querySelector('meta[name="csrf-token"]')?.content;

let autoOn = true;
let intervalSec = 60;
let progTimer = null;
let progVal = 100;
let activeCat = '';
let activeSt  = '';

// --- FUNGSI GLOBAL (Daftarkan ke window) ---

window.checkSingle = async function(id) {
    const btn = document.getElementById('btn-' + id);
    const icon = document.getElementById('icon-' + id);
    if (!btn) return;

    btn.disabled = true;
    icon.className = 'spinning'; icon.textContent = '⟳';

    try {
        const res = await fetch(`/check/${id}`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': getCSRF(), 
                'Accept': 'application/json' 
            }
        });
        const data = await res.json();
        updateCard(data);
        toast(data.status === 'online'
            ? `✓ ${data.status} — ${data.response_ms}ms`
            : `✕ Offline — tidak merespons`, data.status === 'online' ? 'ok' : 'err');
    } catch(e) {
        toast('Request gagal.', 'err');
    } finally {
        btn.disabled = false;
        icon.className = ''; icon.textContent = '⟳';
    }
}

window.checkAll = async function() {
    const btn = document.getElementById('btn-check-all');
    const icon = document.getElementById('icon-all');
    btn.disabled = true;
    icon.className = 'spinning'; icon.textContent = '⟳';
    
    document.querySelectorAll('[id^="icon-"]:not(#icon-all)').forEach(el => {
        el.className = 'spinning'; el.textContent = '⟳';
    });

    try {
        const res = await fetch('/check-all', {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': getCSRF(), 
                'Accept': 'application/json' 
            }
        });
        const data = await res.json();
        data.results.forEach(r => updateCard(r));
        updateTopStats();
        
        const lastScanEl = document.getElementById('last-scan');
        if(lastScanEl) lastScanEl.textContent = 'Terakhir: ' + new Date().toLocaleTimeString('id-ID');
        
        toast(`Selesai — ${data.online}/${data.total} online`, data.offline > 0 ? 'err' : 'ok');
        resetProg();
    } catch(e) {
        console.error(e);
        toast('Gagal cek semua.', 'err');
    } finally {
        btn.disabled = false;
        icon.className = ''; icon.textContent = '⟳';
        document.querySelectorAll('[id^="icon-"]:not(#icon-all)').forEach(el => {
            el.className = ''; el.textContent = '⟳';
        });
    }
}

window.filterCat = function(cat) {
    activeCat = cat;
    document.querySelectorAll('.cat-sidebar-btn').forEach(b => {
        b.classList.remove('bg-blue-500/10', 'text-blue-400');
        b.classList.add('text-gray-400');
    });
    const activeId = cat ? 'cat-btn-' + cat.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,'') : 'cat-btn-all';
    const btn = document.getElementById(activeId);
    if (btn) { btn.classList.add('bg-blue-500/10', 'text-blue-400'); btn.classList.remove('text-gray-400'); }
    applyFilter();
}

window.filterStatus = function(st) {
    activeSt = st;
    document.querySelectorAll('.st-sidebar-btn').forEach(b => {
        b.classList.remove('bg-blue-500/10', 'text-blue-400');
        b.classList.add('text-gray-400');
    });
    const btn = document.getElementById('st-btn-' + (st || 'all'));
    if (btn) { btn.classList.add('bg-blue-500/10', 'text-blue-400'); btn.classList.remove('text-gray-400'); }
    applyFilter();
}

window.filterCards = function() { applyFilter(); }

window.sortCards = function() {
    const sort = document.getElementById('sort-sel').value;
    const grid = document.getElementById('cards-grid');
    const cards = [...grid.querySelectorAll('.service-card')];
    if (sort === 'name') {
        cards.sort((a,b) => a.dataset.name.localeCompare(b.dataset.name));
    } else {
        const order = {online:0, offline:1, unknown:2};
        cards.sort((a,b) => (order[a.dataset.status]||2) - (order[b.dataset.status]||2));
    }
    cards.forEach(c => grid.appendChild(c));
}

window.openEdit = function(id, name, url, cat, dept, auth) {
    document.getElementById('form-edit').action = `/services/${id}`;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-url').value = url;
    document.getElementById('edit-cat').value = cat;
    document.getElementById('edit-dept').value = dept || '';
    document.getElementById('edit-auth').value = auth || 'none';
    document.getElementById('modal-edit').classList.remove('hidden');
}

window.toggleAuto = function() {
    autoOn = !autoOn;
    const sw = document.getElementById('auto-switch');
    const thumb = document.getElementById('auto-thumb');
    const lbl = document.getElementById('auto-label');
    sw.className = `relative w-8 h-4 rounded-full cursor-pointer transition-colors duration-200 ${autoOn ? 'bg-green-500' : 'bg-gray-700'}`;
    thumb.style.left = autoOn ? '17px' : '2px';
    lbl.textContent = autoOn ? 'Aktif' : 'Nonaktif';
    autoOn ? startProg() : stopProg();
}

window.setIntervalTime = function() {
    intervalSec = parseInt(document.getElementById('interval-sel').value);
    resetProg();
}

// --- FUNGSI INTERNAL ---

function applyFilter() {
    const q = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('.service-card').forEach(card => {
        const mq = !q || card.dataset.name.includes(q) || card.dataset.url.includes(q) || card.dataset.dept.includes(q);
        const mc = !activeCat || card.dataset.cat === activeCat;
        const ms = !activeSt  || card.dataset.status === activeSt;
        card.style.display = (mq && mc && ms) ? '' : 'none';
    });
}

function updateCard(data) {
    const card = document.getElementById('card-' + data.id);
    if (!card) return;

    card.className = card.className.replace(/card-(online|offline|unknown)/g, '') + ' card-' + data.status;
    card.dataset.status = data.status;

    const badge = document.getElementById('badge-' + data.id);
    if (badge) {
        if (data.status === 'online') {
            badge.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-900/50 text-green-400 border border-green-800"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Online</span>`;
        } else {
            badge.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-900/50 text-red-400 border border-red-800"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Offline</span>`;
        }
    }

    const msEl = document.getElementById('ms-' + data.id);
    if (msEl) {
        if (data.response_ms) {
            const c = data.response_ms < 150 ? 'text-green-400' : data.response_ms < 400 ? 'text-yellow-400' : 'text-red-400';
            msEl.className = `text-xs mono px-2 py-0.5 rounded ${c}`;
            msEl.textContent = `⚡ ${data.response_ms}ms`;
        } else {
            msEl.className = 'text-xs mono px-2 py-0.5 rounded text-gray-600';
            msEl.textContent = '⚡ —ms';
        }
    }

    const lc = document.getElementById('lastcheck-' + data.id);
    if (lc) lc.textContent = 'baru saja';

    updateTopStats();
}

function updateTopStats() {
    const cards = document.querySelectorAll('.service-card');
    const online = [...cards].filter(c => c.dataset.status === 'online').length;
    const offline = [...cards].filter(c => c.dataset.status === 'offline').length;
    const unknown = [...cards].filter(c => c.dataset.status === 'unknown').length;
    
    const elements = {
        'top-online': online, 'top-offline': offline, 'top-unknown': unknown,
        'sum-total': cards.length, 'sum-online': online, 'sum-offline': offline
    };

    for (const [id, val] of Object.entries(elements)) {
        const el = document.getElementById(id);
        if(el) el.textContent = val;
    }
}

function startProg() {
    stopProg();
    progVal = 100;
    const step = 100 / (intervalSec * 10);
    progTimer = setInterval(() => {
        progVal = Math.max(0, progVal - step);
        const bar = document.getElementById('prog-bar');
        if(bar) bar.style.width = progVal + '%';
        if (progVal <= 0) { 
            window.checkAll(); 
            progVal = 100; 
        }
    }, 100);
}

function stopProg() { if (progTimer) clearInterval(progTimer); }
function resetProg() { stopProg(); if (autoOn) startProg(); }

function toast(msg, type = 'info') {
    const c = document.getElementById('toast-container');
    if(!c) return;
    const el = document.createElement('div');
    const col = { ok:'border-green-700 text-green-300', err:'border-red-700 text-red-300', info:'border-blue-700 text-blue-300' }[type] || '';
    el.className = `mono text-xs bg-gray-900 border-l-2 border border-gray-800 ${col} rounded-lg px-4 py-2.5 shadow-xl toastin min-w-56`;
    el.textContent = msg;
    c.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    startProg();

    ['modal-add','modal-edit'].forEach(id => {
        const modal = document.getElementById(id);
        if(modal) {
            modal.addEventListener('click', e => {
                if (e.target.id === id) e.target.classList.add('hidden');
            });
        }
    });
});