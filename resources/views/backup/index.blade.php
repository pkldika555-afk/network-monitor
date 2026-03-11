<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backup & Restore — NetMonitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mono { font-family: 'IBM Plex Mono', monospace; }
        .terminal-grid { 
            background-image: radial-gradient(circle, #1f2937 1px, transparent 1px); 
            background-size: 24px 24px; 
        }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #030712; }
        ::-webkit-scrollbar-thumb { background: #1f2937; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen terminal-grid">

    <nav class="bg-gray-900/90 backdrop-blur-md border-b border-gray-800 h-14 flex items-center px-6 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto w-full flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-green-500 rounded flex items-center justify-center text-black font-bold text-[10px] mono shadow-[0_0_15px_rgba(34,197,94,0.2)]">NM</div>
                <span class="font-bold text-sm mono tracking-tighter text-white">NetMonitor</span>
            </div>
            <div class="w-px h-4 bg-gray-700"></div>
            <span class="text-[10px] mono text-gray-500 uppercase tracking-[0.2em]">Maintenance_Core</span>
            
            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('services.index') }}"
                    class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-xs mono text-gray-300 transition-all active:scale-95">
                    ← Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-red-900/20 hover:border-red-900/50 hover:text-red-400 border border-gray-700 rounded-lg text-xs mono text-gray-500 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-8 lg:p-12">

        <div class="max-w-3xl mx-auto">
            @if(session('success'))
                <div class="mb-8 flex items-center gap-3 px-4 py-3 bg-green-500/5 border border-green-500/20 rounded-xl text-green-400 text-xs mono animate-in fade-in slide-in-from-top-2">
                    <span class="text-base">✔</span>
                    <span class="font-medium tracking-tight">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-8 flex items-center gap-3 px-4 py-3 bg-red-500/5 border border-red-500/20 rounded-xl text-red-400 text-xs mono animate-in fade-in slide-in-from-top-2">
                    <span class="text-base">✘</span>
                    <span class="font-medium tracking-tight text-red-300">{{ $errors->first() }}</span>
                </div>
            @endif
        </div>

        <div class="mb-12 text-center">
            <h1 class="text-3xl font-bold mono tracking-tighter text-white uppercase">Backup & Restore</h1>
            <div class="flex justify-center items-center gap-3 mt-2">
                <span class="h-px w-8 bg-gray-800"></span>
                <p class="text-xs text-gray-500 mono uppercase tracking-widest leading-none">Database Synchronization Protocol</p>
                <span class="h-px w-8 bg-gray-800"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            @foreach([
                ['💾', 'Services', $stats['services'], 'blue'],
                ['👤', 'Users', $stats['users'], 'purple'],
                ['📋', 'Logs', number_format($stats['logs']), 'emerald'],
                ['🕒', 'System Time', now()->format('H:i'), 'gray'],
            ] as [$icon, $label, $count, $color])
            <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-2xl text-center group hover:bg-gray-900 transition-colors">
                <div class="text-xl mb-2 opacity-80 group-hover:scale-110 transition-transform inline-block">{{ $icon }}</div>
                <div class="text-xl font-bold mono text-white leading-none mb-1">{{ $count }}</div>
                <div class="text-[9px] text-gray-500 mono uppercase tracking-widest">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        <div class="grid md:grid-cols-2 gap-8 items-start">

            <div class="bg-gray-900/40 border border-gray-800 rounded-3xl p-8 flex flex-col h-full hover:border-blue-500/20 transition-all">
                <div class="mb-6">
                    <div class="inline-flex p-3 bg-blue-500/10 rounded-2xl text-blue-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold mono text-white">EXPORT_DATA</h2>
                    <p class="text-xs text-gray-500 mono mt-2 leading-relaxed">Unduh seluruh konfigurasi sistem, data pengguna, dan log aktivitas ke dalam satu file JSON terstruktur.</p>
                </div>

                <div class="mt-auto">
                    <div class="bg-black/20 border border-gray-800/50 rounded-xl p-4 mb-6">
                        <div class="flex justify-between text-[10px] mono text-gray-600 mb-1">
                            <span>READY_STATE</span>
                            <span class="text-blue-500">100%</span>
                        </div>
                        <div class="h-1 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 w-full animate-pulse"></div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('backup.download') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl text-xs font-bold mono transition-all active:scale-[0.98] shadow-lg shadow-blue-950/20">
                            GENERATE_BACKUP.JSON
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-gray-900/40 border border-gray-800 rounded-3xl p-8 flex flex-col h-full hover:border-red-500/20 transition-all">
                <div class="mb-6">
                    <div class="inline-flex p-3 bg-red-500/10 rounded-2xl text-red-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold mono text-white">RESTORE_DATA</h2>
                    <p class="text-xs text-gray-500 mono mt-2 leading-relaxed">Unggah file backup untuk memulihkan keadaan sistem. <span class="text-red-500 font-bold">Data saat ini akan dihapus permanen.</span></p>
                </div>

                <div class="mt-auto">
                    <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="border-2 border-dashed border-gray-800 rounded-2xl p-6 text-center mb-6 hover:border-red-500/40 hover:bg-red-500/5 transition-all cursor-pointer group/file"
                            onclick="document.getElementById('backup-file').click()">
                            <span class="text-2xl block mb-2 group-hover/file:scale-110 transition-transform">📂</span>
                            <span class="text-[10px] mono text-gray-500 uppercase tracking-tighter" id="file-label">Select_Backup_Source</span>
                        </div>
                        
                        <input type="file" id="backup-file" name="backup_file" accept=".json" class="hidden"
                            onchange="updateFileName(this)">

                        <div id="restore-confirm" class="hidden mb-6 p-4 bg-red-500/5 border border-red-500/20 rounded-2xl text-[10px] mono text-red-400 text-center leading-relaxed italic animate-pulse">
                            ⚠️ CAUTION: OVERWRITE_MODE_ACTIVE
                        </div>

                        <button type="button" id="btn-restore-check"
                            onclick="showRestoreConfirm()"
                            class="w-full flex items-center justify-center gap-3 px-6 py-4 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white rounded-2xl text-xs font-bold mono transition-all active:scale-[0.98]">
                            INITIATE_RECOVERY
                        </button>

                        <button type="submit" id="btn-restore-confirm"
                            class="hidden w-full flex items-center justify-center gap-3 px-6 py-4 bg-red-600 hover:bg-red-500 text-white rounded-2xl text-xs font-bold mono transition-all shadow-lg shadow-red-950/20">
                            CONFIRM_SYSTEM_OVERWRITE
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <footer class="mt-16 text-center border-t border-gray-900 pt-8">
            <p class="text-[9px] mono text-gray-600 uppercase tracking-[0.4em]">Secure_Management_Interface_v3 // 2026</p>
        </footer>

    </main>

    <script>
        function updateFileName(input) {
            const label = document.getElementById('file-label');
            if(input.files.length > 0) {
                label.textContent = input.files[0].name;
                label.classList.add('text-red-400', 'font-bold');
            }
        }

        function showRestoreConfirm() {
            const fileInput = document.getElementById('backup-file');
            if (!fileInput.files.length) {
                alert('ERROR: No source file detected.');
                return;
            }
            document.getElementById('restore-confirm').classList.remove('hidden');
            document.getElementById('btn-restore-confirm').classList.remove('hidden');
            document.getElementById('btn-restore-check').classList.add('hidden');
        }
    </script>
</body>
</html>