<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Data — NetMonitor</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md px-4">

        {{-- LOGO --}}
        <div class="text-center mb-8">
            <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center text-black font-bold mono mx-auto mb-3">NM</div>
            <h1 class="text-lg font-bold mono">NetMonitor</h1>
            <p class="text-xs text-gray-500 mono mt-1">Restore Data Awal</p>
        </div>

        {{-- CARD --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">

            @if(session('success'))
                <div class="mb-4 px-4 py-2.5 bg-green-900/30 border border-green-800 rounded-lg text-green-400 text-xs mono">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 px-4 py-2.5 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-xs mono">
                    ✗ {{ $errors->first() }}
                </div>
            @endif

            <div class="flex items-center gap-2 mb-1">
                <span>📥</span>
                <h2 class="text-sm font-bold mono">Import Backup JSON</h2>
            </div>
            <p class="text-xs text-gray-500 mono mb-5">
                Gunakan halaman ini untuk restore data ke instalasi baru NetMonitor sebelum login.
            </p>

            <form method="POST" action="{{ route('backup.restore-awal.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-700 rounded-lg p-5 text-center mb-4 hover:border-gray-600 transition cursor-pointer"
                    onclick="document.getElementById('file-awal').click()">
                    <div class="text-3xl mb-2">📂</div>
                    <div class="text-xs mono text-gray-400" id="file-label-awal">Klik untuk pilih file .json</div>
                </div>
                <input type="file" id="file-awal" name="backup_file" accept=".json" class="hidden"
                    onchange="document.getElementById('file-label-awal').textContent = this.files[0]?.name ?? 'Klik untuk pilih file .json'">

                <button type="submit"
                    class="w-full py-2.5 bg-green-600 hover:bg-green-500 text-black font-bold rounded-lg text-xs mono transition">
                    ↑ Restore & Login
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-xs mono text-gray-600 hover:text-gray-400 transition">
                ← Kembali ke Login
            </a>
        </div>
    </div>
</body>
</html>