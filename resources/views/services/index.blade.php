<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Network Monitor</title>
    @vite(['resources/css/app.css'])
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mono {
            font-family: 'IBM Plex Mono', monospace;
        }

        .card-online {
            border-left: 4px solid #22c55e;
        }

        .card-offline {
            border-left: 4px solid #ef4444;
        }

        .card-unknown {
            border-left: 4px solid #6b7280;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinning {
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }

        @keyframes fadein {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fadein {
            animation: fadein 0.2s ease;
        }
    </style>
</head>

<body class="bg-gray-950 text-gray-100 min-h-screen">

    <nav class="bg-gray-900 border-b border-gray-800 px-6 h-14 flex items-center justify-between sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <div
                class="w-7 h-7 bg-green-500 rounded-md flex items-center justify-center text-black font-bold text-xs mono">
                NM</div>
            <span class="font-bold text-sm mono">NetworkMonitor</span>
            <span class="text-gray-600 text-sm">—</span>
            <span class="text-gray-400 text-xs mono">Internal LAN</span>
        </div>
        <div class="flex items-center gap-3">

            <span class="text-xs mono text-gray-400">
                <span class="text-green-400 font-semibold"
                    id="top-online">{{ $services->where('status', 'online')->count() }}</span> online
                &nbsp;·&nbsp;
                <span class="text-red-400 font-semibold"
                    id="top-offline">{{ $services->where('status', 'offline')->count() }}</span> offline
                &nbsp;·&nbsp;
                <span class="text-gray-500 font-semibold"
                    id="top-unknown">{{ $services->where('status', 'unknown')->count() }}</span> unknown
            </span>
            <button onclick="checkAll()" id="btn-check-all"
                class="flex items-center gap-2 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-xs font-semibold transition">
                <span id="icon-all">⟳</span> Cek Semua
            </button>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="flex items-center gap-2 px-3 py-1.5 bg-green-500 hover:bg-green-400 text-black rounded-lg text-xs font-bold transition">
                + Tambah
            </button>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">


        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-green-900/40 border border-green-700 rounded-lg text-green-300 text-sm fadein">
                ✓ {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center gap-3 mb-6">
            <input type="text" id="search" placeholder="Cari nama, IP, departemen..." oninput="filterCards()"
                class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-sm mono text-gray-200 placeholder-gray-600 outline-none focus:border-blue-500 transition">
            <select id="filter-cat" onchange="filterCards()"
                class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-300 outline-none">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
            <select id="filter-status" onchange="filterCards()"
                class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-300 outline-none">
                <option value="">Semua Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="unknown">Unknown</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="cards-grid">
            @forelse($services as $svc)
                <div class="service-card bg-gray-900 border border-gray-800 rounded-xl overflow-hidden card-{{ $svc->status }} fadein"
                    data-name="{{ strtolower($svc->name) }}" data-url="{{ strtolower($svc->url) }}"
                    data-dept="{{ strtolower($svc->department) }}" data-cat="{{ $svc->category }}"
                    data-status="{{ $svc->status }}" id="card-{{ $svc->id }}">

                    <div class="p-4 pb-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-sm text-white truncate">{{ $svc->name }}</div>
                                <div class="mono text-xs text-gray-500 mt-0.5 truncate">{{ $svc->url }}</div>
                            </div>

                            <div id="badge-{{ $svc->id }}" class="flex-shrink-0">
                                @if($svc->status === 'online')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-green-900/50 text-green-400 border border-green-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Online
                                    </span>
                                @elseif($svc->status === 'offline')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-red-900/50 text-red-400 border border-red-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Offline
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-gray-800 text-gray-400 border border-gray-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Unknown
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-900/30 text-blue-400 border border-blue-800/50 mono">
                                {{ $svc->category }}
                            </span>
                            @if($svc->department)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-orange-900/20 text-orange-400 border border-orange-800/40 mono">
                                    {{ $svc->department }}
                                </span>
                            @endif
                            @if($svc->response_ms)
                                            <span id="ms-{{ $svc->id }}"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs mono
                                {{ $svc->response_ms < 150 ? 'text-green-400' : ($svc->response_ms < 400 ? 'text-yellow-400' : 'text-red-400') }}">
                                                ⚡ {{ $svc->response_ms }}ms
                                            </span>
                            @else
                                <span id="ms-{{ $svc->id }}"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs mono text-gray-600">⚡ —ms</span>
                            @endif
                            @if($svc->auth_type !== 'none')
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-800 text-gray-500 mono">
                                    🔒 {{ $svc->auth_type }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="px-4 py-2.5 border-t border-gray-800 flex items-center justify-between">
                        <div id="lastcheck-{{ $svc->id }}" class="text-xs mono text-gray-600">
                            @if($svc->last_checked_at)
                                {{ $svc->last_checked_at->diffForHumans() }}
                            @else
                                Belum pernah dicek
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="checkSingle({{ $svc->id }})" id="btn-{{ $svc->id }}"
                                class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-700 hover:bg-gray-700 text-gray-400 hover:text-white text-sm transition"
                                title="Cek sekarang">
                                <span id="icon-{{ $svc->id }}">⟳</span>
                            </button>
                            <button
                                onclick="openEdit({{ $svc->id }}, '{{ addslashes($svc->name) }}', '{{ addslashes($svc->url) }}', '{{ $svc->category }}', '{{ $svc->department }}', '{{ $svc->auth_type }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-700 hover:bg-gray-700 text-gray-400 hover:text-white text-sm transition"
                                title="Edit">✎
                            </button>
                            <form method="POST" action="{{ route('services.destroy', $svc->id) }}"
                                onsubmit="return confirm('Hapus {{ $svc->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-700 hover:bg-red-900/40 hover:border-red-700 text-gray-400 hover:text-red-400 text-sm transition"
                                    title="Hapus">✕
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-20 text-gray-600">
                    <div class="text-4xl mb-4">📡</div>
                    <p class="text-sm">Belum ada service. Klik "+ Tambah" untuk mulai.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="modal-add"
        class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md fadein">
            <div class="mono font-semibold text-base mb-1">+ Tambah Service</div>
            <div class="text-xs text-gray-500 mb-5">Daftarkan IP atau URL internal untuk dipantau</div>
            <form method="POST" action="{{ route('services.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Nama Service</label>
                        <input name="name" required placeholder="e.g. Camera Lobby, Timesheet App"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">URL / IP
                            Address</label>
                        <input name="url" required placeholder="http://192.168.100.51/timesheet/"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Kategori</label>
                            <input name="category" placeholder="Web App / IP Camera / ..."
                                class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Departemen /
                                PIC</label>
                            <input name="department" placeholder="HRD / IT / Produksi"
                                class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Auth Type</label>
                        <select name="auth_type"
                            class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono">
                            <option value="none">Tidak ada (terbuka)</option>
                            <option value="bearer">Bearer Token</option>
                            <option value="basic">Basic Auth (user:pass)</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold bg-green-500 hover:bg-green-400 text-black rounded-lg transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-edit"
        class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md fadein">
            <div class="mono font-semibold text-base mb-1">✎ Edit Service</div>
            <div class="text-xs text-gray-500 mb-5">Update data service</div>
            <form id="form-edit" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Nama Service</label>
                        <input id="edit-name" name="name" required
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">URL / IP
                            Address</label>
                        <input id="edit-url" name="url" required
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Kategori</label>
                            <input id="edit-cat" name="category"
                                class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                        </div>
                        <div>
                            <label
                                class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Departemen</label>
                            <input id="edit-dept" name="department"
                                class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1.5">Auth Type</label>
                        <select id="edit-auth" name="auth_type"
                            class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200 outline-none mono">
                            <option value="none">Tidak ada</option>
                            <option value="bearer">Bearer Token</option>
                            <option value="basic">Basic Auth</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-white border border-gray-700 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold bg-blue-500 hover:bg-blue-400 text-white rounded-lg transition">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        async function checkSingle(id) {
            const btn = document.getElementById('btn-' + id);
            const icon = document.getElementById('icon-' + id);
            btn.disabled = true;
            icon.className = 'spinning';
            icon.textContent = '⟳';

            try {
                const res = await fetch(`/check/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                });
                const data = await res.json();
                updateCard(data);
                toast(
                    data.status === 'online'
                        ? `✓ Online — ${data.response_ms}ms (HTTP ${data.http_code})`
                        : `✕ Offline — tidak merespons`,
                    data.status === 'online' ? 'ok' : 'err'
                );
            } catch (e) {
                toast('Gagal melakukan request.', 'err');
            } finally {
                btn.disabled = false;
                icon.className = '';
                icon.textContent = '⟳';
            }
        }
        async function checkAll() {
            const btn = document.getElementById('btn-check-all');
            const icon = document.getElementById('icon-all');
            btn.disabled = true;
            icon.className = 'spinning';
            icon.textContent = '⟳';

            document.querySelectorAll('[id^="icon-"]').forEach(el => {
                if (el.id !== 'icon-all') { el.className = 'spinning'; el.textContent = '⟳'; }
            });

            try {
                const res = await fetch('/check-all', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                });
                const data = await res.json();

                data.results.forEach(r => updateCard(r));
                updateTopStats(data.online, data.offline, data.total - data.online - data.offline);
                toast(`Selesai — ${data.online}/${data.total} online${data.offline ? ', ' + data.offline + ' offline' : ''}`,
                    data.offline > 0 ? 'err' : 'ok');
            } catch (e) {
                toast('Gagal cek semua service.', 'err');
            } finally {
                btn.disabled = false;
                icon.className = '';
                icon.textContent = '⟳';
                document.querySelectorAll('[id^="icon-"]').forEach(el => {
                    if (el.id !== 'icon-all') { el.className = ''; el.textContent = '⟳'; }
                });
            }
        }

        function updateCard(data) {
            const card = document.getElementById('card-' + data.id);
            if (!card) return;

            card.className = card.className.replace(/card-(online|offline|unknown)/g, '');
            card.classList.add('card-' + data.status);
            card.dataset.status = data.status;

            const badge = document.getElementById('badge-' + data.id);
            if (data.status === 'online') {
                badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-green-900/50 text-green-400 border border-green-800"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>Online</span>`;
            } else {
                badge.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-red-900/50 text-red-400 border border-red-800"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Offline</span>`;
            }

            const msEl = document.getElementById('ms-' + data.id);
            if (msEl) {
                if (data.response_ms) {
                    const color = data.response_ms < 150 ? 'text-green-400' : data.response_ms < 400 ? 'text-yellow-400' : 'text-red-400';
                    msEl.className = `inline-flex items-center px-2 py-0.5 rounded text-xs mono ${color}`;
                    msEl.textContent = `⚡ ${data.response_ms}ms`;
                } else {
                    msEl.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs mono text-gray-600';
                    msEl.textContent = '⚡ —ms';
                }
            }

            const lc = document.getElementById('lastcheck-' + data.id);
            if (lc) lc.textContent = 'baru saja';

            const online = document.querySelectorAll('[data-status="online"]').length;
            const offline = document.querySelectorAll('[data-status="offline"]').length;
            const unknown = document.querySelectorAll('[data-status="unknown"]').length;
            updateTopStats(online, offline, unknown);
        }

        function updateTopStats(online, offline, unknown) {
            document.getElementById('top-online').textContent = online;
            document.getElementById('top-offline').textContent = offline;
            document.getElementById('top-unknown').textContent = unknown;
        }

        function filterCards() {
            const q = document.getElementById('search').value.toLowerCase();
            const cat = document.getElementById('filter-cat').value;
            const st = document.getElementById('filter-status').value;

            document.querySelectorAll('.service-card').forEach(card => {
                const matchQ = !q || card.dataset.name.includes(q) || card.dataset.url.includes(q) || card.dataset.dept.includes(q);
                const matchC = !cat || card.dataset.cat === cat;
                const matchSt = !st || card.dataset.status === st;
                card.style.display = (matchQ && matchC && matchSt) ? '' : 'none';
            });
        }

        function openEdit(id, name, url, cat, dept, auth) {
            document.getElementById('form-edit').action = `/services/${id}`;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-url').value = url;
            document.getElementById('edit-cat').value = cat;
            document.getElementById('edit-dept').value = dept;
            document.getElementById('edit-auth').value = auth;
            document.getElementById('modal-edit').classList.remove('hidden');
        }

        function toast(msg, type = 'info') {
            const c = document.getElementById('toast-container');
            const el = document.createElement('div');
            const col = { ok: 'border-green-700 text-green-300', err: 'border-red-700 text-red-300', info: 'border-blue-700 text-blue-300' }[type] || '';
            el.className = `mono text-xs bg-gray-900 border ${col} rounded-lg px-4 py-2.5 shadow-xl fadein min-w-[240px]`;
            el.textContent = msg;
            c.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        }

        ['modal-add', 'modal-edit'].forEach(id => {
            document.getElementById(id).addEventListener('click', e => {
                if (e.target.id === id) e.target.classList.add('hidden');
            });
        });
    </script>
</body>

</html>