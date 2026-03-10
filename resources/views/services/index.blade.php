<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Network Monitor</title>
@vite(['resources/css/app.css'])
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Plus Jakarta Sans', sans-serif; }
  .mono { font-family: 'IBM Plex Mono', monospace; }
  .card-online  { border-left: 3px solid #3fb950; }
  .card-offline { border-left: 3px solid #f85149; }
  .card-unknown { border-left: 3px solid #30363d; }
  @keyframes spin { to { transform: rotate(360deg); } }
  .spinning { animation: spin 0.8s linear infinite; display: inline-block; }
  @keyframes fadein { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
  .fadein { animation: fadein 0.2s ease; }
  @keyframes toastin { from { transform:translateX(40px); opacity:0; } to { transform:translateX(0); opacity:1; } }
  .toastin { animation: toastin 0.25s ease; }
  ::-webkit-scrollbar { width: 4px; }
  ::-webkit-scrollbar-track { background: #0d1117; }
  ::-webkit-scrollbar-thumb { background: #30363d; border-radius: 2px; }
  .progress-fill { transition: width 0.1s linear; }
  .switch-thumb { transition: left 0.2s; }
</style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

<nav class="bg-gray-900 border-b border-gray-800 h-14 flex items-center px-6 gap-4 sticky top-0 z-50">
  <div class="flex items-center gap-2">
    <div class="w-7 h-7 bg-green-500 rounded-md flex items-center justify-center text-black font-bold text-xs mono">NM</div>
    <span class="font-bold text-sm mono">NetMonitor</span>
  </div>
  <div class="w-px h-5 bg-gray-700"></div>

  <div class="flex items-center gap-4 text-xs mono text-gray-500">
    <span><span class="text-green-400 font-semibold" id="top-online">{{ $services->where('status','online')->count() }}</span> online</span>
    <span><span class="text-red-400 font-semibold" id="top-offline">{{ $services->where('status','offline')->count() }}</span> offline</span>
    <span><span class="text-gray-500 font-semibold" id="top-unknown">{{ $services->where('status','unknown')->count() }}</span> unknown</span>
  </div>
  <div class="ml-auto flex items-center gap-2">
    <button onclick="checkAll()" id="btn-check-all"
      class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-xs font-semibold mono transition">
      <span id="icon-all">⟳</span> Cek Semua
    </button>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
      class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 hover:bg-green-400 text-black rounded-lg text-xs font-bold transition">
      + Tambah
    </button>
  </div>
</nav>

<div class="flex" style="min-height: calc(100vh - 56px)">

  <aside class="w-52 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0">
    <div class="p-3 flex-1 overflow-y-auto">

      <div class="text-xs font-bold text-gray-600 tracking-widest uppercase px-2 py-2 mt-1">Kategori</div>

      <button onclick="filterCat('')" id="cat-btn-all"
        class="cat-sidebar-btn w-full flex items-center justify-between px-2 py-1.5 rounded-md text-xs text-blue-400 bg-blue-500/10 mb-0.5 transition">
        <span class="flex items-center gap-2">📡 Semua Service</span>
        <span class="mono text-gray-500 text-xs bg-gray-800 border border-gray-700 rounded-full px-1.5" id="cnt-all">{{ $services->count() }}</span>
      </button>

      @foreach($categories as $cat)
      @php
        $icons = [
          'Web App'   => '🌐',
          'IP Camera' => '📷',
          'Database'  => '🗄️',
          'Printer'   => '🖨️',
          'Server'    => '🖥️',
        ];
        $icon = $icons[$cat->category] ?? '📦';
        $hasErr = $cat->offline_count > 0;
        $slug = Str::slug($cat->category);
      @endphp
      <button onclick="filterCat('{{ $cat->category }}')"
        id="cat-btn-{{ $slug }}"
        class="cat-sidebar-btn w-full flex items-center justify-between px-2 py-1.5 rounded-md text-xs text-gray-400 hover:bg-gray-800 hover:text-gray-200 mb-0.5 transition">
        <span class="flex items-center gap-2">{{ $icon }} {{ $cat->category }}</span>
        <span class="mono text-xs rounded-full px-1.5 {{ $hasErr ? 'text-red-400 bg-red-900/30 border border-red-800/50' : 'text-gray-500 bg-gray-800 border border-gray-700' }}"
              id="cnt-{{ $slug }}">{{ $cat->total }}</span>
      </button>
      @endforeach

      <div class="text-xs font-bold text-gray-600 tracking-widest uppercase px-2 py-2 mt-3">Status</div>
      <button onclick="filterStatus('')" id="st-btn-all"
        class="st-sidebar-btn w-full flex items-center px-2 py-1.5 rounded-md text-xs text-gray-400 hover:bg-gray-800 hover:text-gray-200 mb-0.5 transition">
        ◈ Semua
      </button>
      <button onclick="filterStatus('online')" id="st-btn-online"
        class="st-sidebar-btn w-full flex items-center gap-2 px-2 py-1.5 rounded-md text-xs text-gray-400 hover:bg-gray-800 mb-0.5 transition">
        <span class="w-2 h-2 rounded-full bg-green-500"></span> Online
      </button>
      <button onclick="filterStatus('offline')" id="st-btn-offline"
        class="st-sidebar-btn w-full flex items-center gap-2 px-2 py-1.5 rounded-md text-xs text-gray-400 hover:bg-gray-800 mb-0.5 transition">
        <span class="w-2 h-2 rounded-full bg-red-500"></span> Offline
      </button>
      <button onclick="filterStatus('unknown')" id="st-btn-unknown"
        class="st-sidebar-btn w-full flex items-center gap-2 px-2 py-1.5 rounded-md text-xs text-gray-400 hover:bg-gray-800 mb-0.5 transition">
        <span class="w-2 h-2 rounded-full bg-gray-600"></span> Unknown
      </button>
    </div>
    <div class="m-3 p-3 bg-gray-800/60 border border-gray-700 rounded-lg">
      <div class="text-xs text-gray-600 uppercase tracking-wider mb-2">Auto-Check</div>
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs mono" id="auto-label">Aktif</span>
        <div class="relative w-8 h-4 rounded-full cursor-pointer transition-colors duration-200 bg-green-500" id="auto-switch" onclick="toggleAuto()">
          <div class="absolute w-3 h-3 bg-white rounded-full top-0.5 switch-thumb" id="auto-thumb" style="left:17px"></div>
        </div>
      </div>
      <select id="interval-sel" onchange="setIntervalTime()" class="w-full bg-gray-900 border border-gray-700 rounded text-xs mono text-gray-300 px-2 py-1 outline-none">
        <option value="30">30 detik</option>
        <option value="60" selected>1 menit</option>
        <option value="300">5 menit</option>
        <option value="600">10 menit</option>
      </select>
      <div class="mt-2 h-0.5 bg-gray-700 rounded overflow-hidden">
        <div id="prog-bar" class="h-full bg-green-500 rounded progress-fill" style="width:100%"></div>
      </div>
      <div class="mt-1.5 text-xs mono text-gray-600" id="last-scan">Belum pernah scan</div>
    </div>
  </aside>

  <div class="flex-1 overflow-y-auto p-6">

    @if(session('success'))
    <div class="mb-5 px-4 py-2.5 bg-green-900/30 border border-green-800 rounded-lg text-green-400 text-xs mono fadein">
      ✓ {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-4 gap-3 mb-6">
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center text-base">📡</div>
        <div><div class="mono font-semibold text-2xl text-blue-400" id="sum-total">{{ $services->count() }}</div><div class="text-xs text-gray-600 mt-0.5">Total</div></div>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center text-base">✓</div>
        <div><div class="mono font-semibold text-2xl text-green-400" id="sum-online">{{ $services->where('status','online')->count() }}</div><div class="text-xs text-gray-600 mt-0.5">Online</div></div>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center text-base">✕</div>
        <div><div class="mono font-semibold text-2xl text-red-400" id="sum-offline">{{ $services->where('status','offline')->count() }}</div><div class="text-xs text-gray-600 mt-0.5">Offline</div></div>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center text-base">⚡</div>
        <div>
          <div class="mono font-semibold text-2xl text-yellow-400" id="sum-avg">
            @php
              $times = $services->where('response_ms', '!=', null)->pluck('response_ms');
              echo $times->count() ? round($times->avg()) . 'ms' : '—';
            @endphp
          </div>
          <div class="text-xs text-gray-600 mt-0.5">Avg Response</div>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3 mb-5">
      <div class="flex-1 flex items-center gap-2 bg-gray-900 border border-gray-700 rounded-lg px-3">
        <span class="text-gray-600">⌕</span>
        <input type="text" id="search" placeholder="Cari nama, IP, departemen..."
          oninput="filterCards()"
          class="flex-1 bg-transparent py-2 text-sm mono text-gray-200 placeholder-gray-600 outline-none">
      </div>
      <select id="sort-sel" onchange="sortCards()"
        class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-xs mono text-gray-400 outline-none">
        <option value="name">Nama A-Z</option>
        <option value="status">Status</option>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3" id="cards-grid">
      @forelse($services as $svc)
      <div class="service-card bg-gray-900 border border-gray-800 rounded-xl overflow-hidden card-{{ $svc->status }} fadein"
           data-name="{{ strtolower($svc->name) }}"
           data-url="{{ strtolower($svc->url) }}"
           data-dept="{{ strtolower($svc->department ?? '') }}"
           data-cat="{{ $svc->category }}"
           data-status="{{ $svc->status }}"
           id="card-{{ $svc->id }}">

        <div class="p-4 pb-2">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-bold text-sm truncate">{{ $svc->name }}</div>
              <div class="mono text-xs text-gray-500 mt-0.5 truncate">{{ $svc->url }}</div>
            </div>
            <div id="badge-{{ $svc->id }}" class="flex-shrink-0 mt-0.5">
              @include('services._badge', ['status' => $svc->status])
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-1.5 mt-3">
            <span class="text-xs mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-800/40">{{ $svc->category }}</span>
            @if($svc->department)
            <span class="text-xs mono px-2 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-800/40">{{ $svc->department }}</span>
            @endif
            <span id="ms-{{ $svc->id }}" class="text-xs mono px-2 py-0.5 rounded
              {{ $svc->response_ms ? ($svc->response_ms < 150 ? 'text-green-400' : ($svc->response_ms < 400 ? 'text-yellow-400' : 'text-red-400')) : 'text-gray-600' }}">
              ⚡ {{ $svc->response_ms ? $svc->response_ms.'ms' : '—ms' }}
            </span>
            @if($svc->auth_type && $svc->auth_type !== 'none')
            <span class="text-xs mono px-2 py-0.5 rounded bg-gray-800 text-gray-500">🔒 {{ $svc->auth_type }}</span>
            @endif
          </div>
        </div>

        <div class="px-4 py-2 border-t border-gray-800 flex items-center justify-between">
          <div id="lastcheck-{{ $svc->id }}" class="text-xs mono text-gray-600">
            {{ $svc->last_checked_at ? \Carbon\Carbon::parse($svc->last_checked_at)->diffForHumans() : 'Belum dicek' }}
          </div>
          <div class="flex items-center gap-1.5">
            <button onclick="checkSingle({{ $svc->id }})" id="btn-{{ $svc->id }}"
              class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-gray-700 text-gray-500 hover:text-white text-xs transition" title="Cek">
              <span id="icon-{{ $svc->id }}">⟳</span>
            </button>
            <button onclick="openEdit({{ $svc->id }}, '{{ addslashes($svc->name) }}', '{{ addslashes($svc->url) }}', '{{ $svc->category }}', '{{ $svc->department }}', '{{ $svc->auth_type }}')"
              class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-gray-700 text-gray-500 hover:text-white text-xs transition" title="Edit">✎
            </button>
            <form method="POST" action="{{ route('services.destroy', $svc->id) }}" onsubmit="return confirm('Hapus {{ $svc->name }}?')">
              @csrf @method('DELETE')
              <button type="submit"
                class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-red-900/30 hover:border-red-800 text-gray-500 hover:text-red-400 text-xs transition" title="Hapus">✕
              </button>
            </form>
          </div>
        </div>
      </div>
      @empty
      <div class="col-span-3 text-center py-20 text-gray-700">
        <div class="text-4xl mb-3">📡</div>
        <p class="text-sm">Belum ada service. Klik "+ Tambah" untuk mulai.</p>
      </div>
      @endforelse
    </div>
  </div>
</div>

<div id="modal-add" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md fadein">
    <div class="mono font-semibold mb-1">+ Tambah Service</div>
    <div class="text-xs text-gray-500 mb-5">Daftarkan IP atau URL internal untuk dipantau</div>
    <form method="POST" action="{{ route('services.store') }}">
      @csrf
      <div class="space-y-3">
        <div>
          <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Nama Service</label>
          <input name="name" required placeholder="Camera Lobby, Timesheet App..."
            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
        </div>
        <div>
          <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">URL / IP</label>
          <input name="url" required placeholder="http://192.168.100.51/timesheet/"
            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
            <input name="category" placeholder="Web App / IP Camera..."
              class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
          </div>
          <div>
            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Departemen</label>
            <input name="department" placeholder="HRD / IT / Produksi"
              class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
          </div>
        </div>
        <div>
          <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Auth Type</label>
          <select name="auth_type" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none">
            <option value="none">Tidak ada</option>
            <option value="bearer">Bearer Token</option>
            <option value="basic">Basic Auth</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
          class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
        <button type="submit" class="px-4 py-2 text-xs font-bold bg-green-500 hover:bg-green-400 text-black rounded-lg transition">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md fadein">
    <div class="mono font-semibold mb-1">✎ Edit Service</div>
    <div class="text-xs text-gray-500 mb-5">Update data service</div>
    <form id="form-edit" method="POST">
      @csrf @method('PUT')
      <div class="space-y-3">
        <div>
          <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Nama Service</label>
          <input id="edit-name" name="name" required class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
        </div>
        <div>
          <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">URL / IP</label>
          <input id="edit-url" name="url" required class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
            <input id="edit-cat" name="category" class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
          </div>
          <div>
            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Departemen</label>
            <input id="edit-dept" name="department" class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
          </div>
        </div>
        <div>
          <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Auth Type</label>
          <select id="edit-auth" name="auth_type" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none">
            <option value="none">Tidak ada</option>
            <option value="bearer">Bearer Token</option>
            <option value="basic">Basic Auth</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
          class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
        <button type="submit" class="px-4 py-2 text-xs font-bold bg-blue-500 hover:bg-blue-400 text-white rounded-lg transition">Update</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let autoOn = true;
let intervalSec = 60;
let progTimer = null;
let progVal = 100;
let activeCat = '';
let activeSt  = '';

async function checkSingle(id) {
  const btn  = document.getElementById('btn-' + id);
  const icon = document.getElementById('icon-' + id);
  btn.disabled = true;
  icon.className = 'spinning'; icon.textContent = '⟳';

  try {
    const res  = await fetch(`/check/${id}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    updateCard(data);
    toast(data.status === 'online'
      ? `✓ ${data.status} — ${data.response_ms}ms (HTTP ${data.http_code})`
      : `✕ Offline — tidak merespons`, data.status === 'online' ? 'ok' : 'err');
  } catch(e) {
    toast('Request gagal.', 'err');
  } finally {
    btn.disabled = false;
    icon.className = ''; icon.textContent = '⟳';
  }
}

async function checkAll() {
  const btn  = document.getElementById('btn-check-all');
  const icon = document.getElementById('icon-all');
  btn.disabled = true;
  icon.className = 'spinning'; icon.textContent = '⟳';
  document.querySelectorAll('[id^="icon-"]:not(#icon-all)').forEach(el => {
    el.className = 'spinning'; el.textContent = '⟳';
  });

  try {
    const res  = await fetch('/check-all', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const data = await res.json();
    data.results.forEach(r => updateCard(r));
    updateTopStats();
    document.getElementById('last-scan').textContent = 'Terakhir: ' + new Date().toLocaleTimeString('id-ID');
    toast(`Selesai — ${data.online}/${data.total} online`, data.offline > 0 ? 'err' : 'ok');
    resetProg();
  } catch(e) {
    toast('Gagal cek semua.', 'err');
  } finally {
    btn.disabled = false;
    icon.className = ''; icon.textContent = '⟳';
    document.querySelectorAll('[id^="icon-"]:not(#icon-all)').forEach(el => {
      el.className = ''; el.textContent = '⟳';
    });
  }
}

function updateCard(data) {
  const card = document.getElementById('card-' + data.id);
  if (!card) return;

  card.className = card.className.replace(/card-(online|offline|unknown)/g, '') + ' card-' + data.status;
  card.dataset.status = data.status;

  const badge = document.getElementById('badge-' + data.id);
  if (data.status === 'online') {
    badge.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-900/50 text-green-400 border border-green-800"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Online</span>`;
  } else {
    badge.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-900/50 text-red-400 border border-red-800"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Offline</span>`;
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
  const cards  = document.querySelectorAll('.service-card');
  const online  = [...cards].filter(c => c.dataset.status === 'online').length;
  const offline = [...cards].filter(c => c.dataset.status === 'offline').length;
  const unknown = [...cards].filter(c => c.dataset.status === 'unknown').length;
  document.getElementById('top-online').textContent  = online;
  document.getElementById('top-offline').textContent = offline;
  document.getElementById('top-unknown').textContent = unknown;
  document.getElementById('sum-total').textContent   = cards.length;
  document.getElementById('sum-online').textContent  = online;
  document.getElementById('sum-offline').textContent = offline;
}

function filterCat(cat) {
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

function filterStatus(st) {
  activeSt = st;
  document.querySelectorAll('.st-sidebar-btn').forEach(b => {
    b.classList.remove('bg-blue-500/10', 'text-blue-400');
    b.classList.add('text-gray-400');
  });
  const btn = document.getElementById('st-btn-' + (st || 'all'));
  if (btn) { btn.classList.add('bg-blue-500/10', 'text-blue-400'); btn.classList.remove('text-gray-400'); }
  applyFilter();
}

function filterCards() { applyFilter(); }

function applyFilter() {
  const q = document.getElementById('search').value.toLowerCase();
  document.querySelectorAll('.service-card').forEach(card => {
    const mq = !q || card.dataset.name.includes(q) || card.dataset.url.includes(q) || card.dataset.dept.includes(q);
    const mc = !activeCat || card.dataset.cat === activeCat;
    const ms = !activeSt  || card.dataset.status === activeSt;
    card.style.display = (mq && mc && ms) ? '' : 'none';
  });
}

function sortCards() {
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

function openEdit(id, name, url, cat, dept, auth) {
  document.getElementById('form-edit').action = `/services/${id}`;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-url').value  = url;
  document.getElementById('edit-cat').value  = cat;
  document.getElementById('edit-dept').value = dept || '';
  document.getElementById('edit-auth').value = auth || 'none';
  document.getElementById('modal-edit').classList.remove('hidden');
}
function toggleAuto() {
  autoOn = !autoOn;
  const sw    = document.getElementById('auto-switch');
  const thumb = document.getElementById('auto-thumb');
  const lbl   = document.getElementById('auto-label');
  sw.className = `relative w-8 h-4 rounded-full cursor-pointer transition-colors duration-200 ${autoOn ? 'bg-green-500' : 'bg-gray-700'}`;
  thumb.style.left = autoOn ? '17px' : '2px';
  lbl.textContent  = autoOn ? 'Aktif' : 'Nonaktif';
  autoOn ? startProg() : stopProg();
}

function setIntervalTime() {
  intervalSec = parseInt(document.getElementById('interval-sel').value);
  resetProg();
}

function startProg() {
  stopProg();
  progVal = 100;
  const step = 100 / (intervalSec * 10);
  progTimer = setInterval(() => {
    progVal = Math.max(0, progVal - step);
    document.getElementById('prog-bar').style.width = progVal + '%';
    if (progVal <= 0) { checkAll(); progVal = 100; }
  }, 100);
}
function stopProg() { if (progTimer) clearInterval(progTimer); }
function resetProg() { stopProg(); if (autoOn) startProg(); }

function toast(msg, type = 'info') {
  const c   = document.getElementById('toast-container');
  const el  = document.createElement('div');
  const col = { ok:'border-green-700 text-green-300', err:'border-red-700 text-red-300', info:'border-blue-700 text-blue-300' }[type] || '';
  el.className = `mono text-xs bg-gray-900 border-l-2 border border-gray-800 ${col} rounded-lg px-4 py-2.5 shadow-xl toastin min-w-56`;
  el.textContent = msg;
  c.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

['modal-add','modal-edit'].forEach(id => {
  document.getElementById(id).addEventListener('click', e => {
    if (e.target.id === id) e.target.classList.add('hidden');
  });
});

startProg();
</script>
</body>
</html>