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