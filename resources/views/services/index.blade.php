<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Network Monitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css', 'resources/js/monitor.js', 'resources/js/assign.js', 'resources/js/sound.js', 'resources/js/delete.js'])
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
            <button id="mute-btn" onclick="toggleMute()" title="Sound on — click to mute"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-700 text-gray-500 hover:text-white transition text-sm">
                <span id="mute-icon">🔔</span>
            </button>
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

        @include('services.partials._aside')

        <div class="flex-1 overflow-y-auto p-6">

            @if(session('success'))
                <div
                    class="mb-5 px-4 py-2.5 bg-green-900/30 border border-green-800 rounded-lg text-green-400 text-xs mono fadein">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @include('services.partials._stats')

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
    @include('services.partials._modal_assign')
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
</body>

</html>