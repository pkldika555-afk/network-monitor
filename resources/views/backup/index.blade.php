<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backup & Restore — NetMonitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css'])
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

    {{-- NAV --}}
    <nav class="bg-gray-900 border-b border-gray-800 h-14 flex items-center px-6 gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-green-500 rounded-md flex items-center justify-center text-black font-bold text-xs mono">NM</div>
            <span class="font-bold text-sm mono">NetMonitor</span>
        </div>
        <div class="w-px h-5 bg-gray-700"></div>
        <span class="text-xs mono text-gray-500">Backup & Restore</span>
        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('services.index') }}"
                class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-xs mono text-gray-400 transition">
                ← Dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-red-900/30 hover:border-red-800 hover:text-red-400 border border-gray-700 rounded-lg text-xs mono text-gray-500 transition">
                    ⏻ Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="flex" style="min-height: calc(100vh - 56px)">

        <div class="flex-1 overflow-y-auto p-6">

            @if(session('success'))
                <div class="mb-5 px-4 py-2.5 bg-green-900/30 border border-green-800 rounded-lg text-green-400 text-xs mono">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 px-4 py-2.5 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-xs mono">
                    ✗ {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-6">
                <h1 class="text-lg font-bold mono">Backup & Restore</h1>
                <p class="text-xs text-gray-500 mono mt-0.5">Kelola data backup NetMonitor dalam format JSON</p>
            </div>

            <div class="grid grid-cols-4 gap-3 mb-6">
                @foreach([
                    ['💾', 'Services', $stats['services']],
                    ['👤', 'Users', $stats['users']],
                    ['📋', 'Check Logs', $stats['logs']],
                ] as [$icon, $label, $count])
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <div class="text-lg mb-1">{{ $icon }}</div>
                    <div class="text-xl font-bold mono">{{ number_format($count) }}</div>
                    <div class="text-xs text-gray-500 mono">{{ $label }}</div>
                </div>
                @endforeach
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <div class="text-lg mb-1">🕐</div>
                    <div class="text-xs font-bold mono text-gray-300">{{ now()->format('d/m/Y') }}</div>
                    <div class="text-xs text-gray-500 mono">Hari ini</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-base">📤</span>
                        <h2 class="text-sm font-bold mono">Download Backup</h2>
                    </div>
                    <p class="text-xs text-gray-500 mono mb-4">Export semua data (services, users, logs) ke file JSON.</p>

                    <div class="bg-gray-800/60 border border-gray-700 rounded-lg p-3 mb-4 text-xs mono text-gray-400 space-y-1">
                        <div class="flex justify-between"><span>Services</span><span class="text-gray-200">{{ $stats['services'] }}</span></div>
                        <div class="flex justify-between"><span>Users</span><span class="text-gray-200">{{ $stats['users'] }}</span></div>
                        <div class="flex justify-between"><span>Check Logs</span><span class="text-gray-200">{{ number_format($stats['logs']) }}</span></div>
                    </div>

                    <form method="POST" action="{{ route('backup.download') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold mono transition">
                            ↓ Download JSON
                        </button>
                    </form>
                </div>

                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-base">📥</span>
                        <h2 class="text-sm font-bold mono">Restore Backup</h2>
                    </div>
                    <p class="text-xs text-gray-500 mono mb-4">Import file JSON backup. <span class="text-red-400">Data saat ini akan ditimpa!</span></p>

                    <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="border-2 border-dashed border-gray-700 rounded-lg p-4 text-center mb-3 hover:border-gray-600 transition cursor-pointer"
                            onclick="document.getElementById('backup-file').click()">
                            <div class="text-2xl mb-1">📂</div>
                            <div class="text-xs mono text-gray-400" id="file-label">Klik untuk pilih file .json</div>
                        </div>
                        <input type="file" id="backup-file" name="backup_file" accept=".json" class="hidden"
                            onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Klik untuk pilih file .json'">

                        <div id="restore-confirm" class="hidden mb-3 p-3 bg-red-900/20 border border-red-800/50 rounded-lg text-xs mono text-red-400">
                            ⚠ Semua data saat ini akan dihapus dan diganti dengan data dari backup. Lanjutkan?
                        </div>

                        <button type="button" id="btn-restore-check"
                            onclick="showRestoreConfirm()"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 border border-gray-600 text-white rounded-lg text-xs font-bold mono transition">
                            ↑ Restore Data
                        </button>
                        <button type="submit" id="btn-restore-confirm"
                            class="hidden w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-700 hover:bg-red-600 text-white rounded-lg text-xs font-bold mono transition mt-2">
                            ⚠ Ya, Timpa & Restore
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function showRestoreConfirm() {
            const fileInput = document.getElementById('backup-file');
            if (!fileInput.files.length) {
                alert('Pilih file backup dulu!');
                return;
            }
            document.getElementById('restore-confirm').classList.remove('hidden');
            document.getElementById('btn-restore-confirm').classList.remove('hidden');
            document.getElementById('btn-restore-check').classList.add('hidden');
        }
    </script>
</body>
</html>