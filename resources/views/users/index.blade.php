<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Users — NetMonitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css', 'resources/js/users.js'])
  
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mono { font-family: 'IBM Plex Mono', monospace; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

    <nav class="bg-gray-900 border-b border-gray-800 h-14 flex items-center px-6 gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-green-500 rounded-md flex items-center justify-center text-black font-bold text-xs mono">NM</div>
            <span class="font-bold text-sm mono">NetMonitor</span>
        </div>
        <div class="w-px h-5 bg-gray-700"></div>
        <a href="{{ route('services.index') }}" class="text-xs mono text-gray-500 hover:text-white transition">← Back to Monitor</a>
        <div class="ml-auto flex items-center gap-2">
            <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')"
                class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 hover:bg-green-400 text-black rounded-lg text-xs font-bold transition">
                + Tambah User
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-red-900/30 hover:text-red-400 border border-gray-700 rounded-lg text-xs mono text-gray-500 transition">
                    ⏻ Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-8">

        @if(session('success'))
        <div class="mb-5 px-4 py-2.5 bg-green-900/30 border border-green-800 rounded-lg text-green-400 text-xs mono">
            ✓ {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-5 px-4 py-2.5 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-xs mono">
            ✕ {{ session('error') }}
        </div>
        @endif

        <div class="font-bold text-base mb-1">User Management</div>
        <div class="text-xs text-gray-500 mono mb-6">{{ $users->count() }} user terdaftar</div>

        @include('users.partials._table')
    </div>

    @include('users.partials._modal_add')
    @include('users.partials._modal_edit')
    
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    @vite(['resources/js/users.js'])
</body>
</html>