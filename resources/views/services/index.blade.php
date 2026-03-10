<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Network Monitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css', 'resources/js/monitor.js'])
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

</head>

<body class="bg-gray-950 text-gray-100 min-h-screen">

    <nav class="bg-gray-900 border-b border-gray-800 h-14 flex items-center px-6 gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div
                class="w-7 h-7 bg-green-500 rounded-md flex items-center justify-center text-black font-bold text-xs mono">
                NM</div>
            <span class="font-bold text-sm mono">NetMonitor</span>
        </div>
        <div class="w-px h-5 bg-gray-700"></div>

        <div class="flex items-center gap-4 text-xs mono text-gray-500">
            <span><span class="text-green-400 font-semibold"
                    id="top-online">{{ $services->where('status', 'online')->count() }}</span> online</span>
            <span><span class="text-red-400 font-semibold"
                    id="top-offline">{{ $services->where('status', 'offline')->count() }}</span> offline</span>
            <span><span class="text-gray-500 font-semibold"
                    id="top-unknown">{{ $services->where('status', 'unknown')->count() }}</span> unknown</span>
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
                    <span class="mono text-gray-500 text-xs bg-gray-800 border border-gray-700 rounded-full px-1.5"
                        id="cnt-all">{{ $services->count() }}</span>
                </button>

                @foreach($categories as $cat)
                    @php
                        $icons = [
                            'Web App' => '🌐',
                            'IP Camera' => '📷',
                            'Database' => '🗄️',
                            'Printer' => '🖨️',
                            'Server' => '🖥️',
                        ];
                        $icon = $icons[$cat->category] ?? '📦';
                        $hasErr = $cat->offline_count > 0;
                        $slug = Str::slug($cat->category);
                      @endphp
                    <button onclick="filterCat('{{ $cat->category }}')" id="cat-btn-{{ $slug }}"
                        class="cat-sidebar-btn w-full flex items-center justify-between px-2 py-1.5 rounded-md text-xs text-gray-400 hover:bg-gray-800 hover:text-gray-200 mb-0.5 transition">
                        <span class="flex items-center gap-2">{{ $icon }} {{ $cat->category }}</span>
                        <span
                            class="mono text-xs rounded-full px-1.5 {{ $hasErr ? 'text-red-400 bg-red-900/30 border border-red-800/50' : 'text-gray-500 bg-gray-800 border border-gray-700' }}"
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
                    <div class="relative w-8 h-4 rounded-full cursor-pointer transition-colors duration-200 bg-green-500"
                        id="auto-switch" onclick="toggleAuto()">
                        <div class="absolute w-3 h-3 bg-white rounded-full top-0.5 switch-thumb" id="auto-thumb"
                            style="left:17px"></div>
                    </div>
                </div>
                <select id="interval-sel" onchange="setIntervalTime()"
                    class="w-full bg-gray-900 border border-gray-700 rounded text-xs mono text-gray-300 px-2 py-1 outline-none">
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
                <div
                    class="mb-5 px-4 py-2.5 bg-green-900/30 border border-green-800 rounded-lg text-green-400 text-xs mono fadein">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-4 gap-3 mb-6">
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center text-base">📡</div>
                    <div>
                        <div class="mono font-semibold text-2xl text-blue-400" id="sum-total">{{ $services->count() }}
                        </div>
                        <div class="text-xs text-gray-600 mt-0.5">Total</div>
                    </div>
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center text-base">✓</div>
                    <div>
                        <div class="mono font-semibold text-2xl text-green-400" id="sum-online">
                            {{ $services->where('status', 'online')->count() }}</div>
                        <div class="text-xs text-gray-600 mt-0.5">Online</div>
                    </div>
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center text-base">✕</div>
                    <div>
                        <div class="mono font-semibold text-2xl text-red-400" id="sum-offline">
                            {{ $services->where('status', 'offline')->count() }}</div>
                        <div class="text-xs text-gray-600 mt-0.5">Offline</div>
                    </div>
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
                    <input type="text" id="search" placeholder="Cari nama, IP, departemen..." oninput="filterCards()"
                        class="flex-1 bg-transparent py-2 text-sm mono text-gray-200 placeholder-gray-600 outline-none">
                </div>
                <select id="sort-sel" onchange="sortCards()"
                    class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-xs mono text-gray-400 outline-none">
                    <option value="name">Nama A-Z</option>
                    <option value="status">Status</option>
                </select>
            </div>

           @include('services.partials._table')
        </div>
    </div>
    @include('services.partials._modal_add')
    @include('services.partials._modal_edit')
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
</body>
</html>