<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NetMonitor</title>
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
    </style>
</head>

<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        <div class="flex flex-col items-center mb-8">
            <div
                class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center text-black font-bold text-lg mono mb-3">
                NM
            </div>
            <div class="font-bold text-lg mono">NetMonitor</div>
            <div class="text-xs text-gray-500 mt-1">Internal LAN Monitor</div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <div class="font-semibold text-sm mb-1">Masuk ke Dashboard</div>
            <div class="text-xs text-gray-500 mb-5">Gunakan akun yang sudah terdaftar</div>

            @if($errors->any())
                <div class="mb-4 px-3 py-2.5 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-xs mono">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">NRP / Email</label>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                            placeholder="12345 atau admin@netmonitor.local"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="remember" id="remember" class="w-3.5 h-3.5 accent-green-500">
                        <label for="remember" class="text-xs text-gray-500 cursor-pointer">Ingat saya</label>
                    </div>
                </div>
                <button type="submit"
                    class="w-full mt-5 py-2.5 bg-green-500 hover:bg-green-400 text-black font-bold text-sm rounded-lg transition">
                    Masuk →
                </button>
            </form>
            <a href="{{ route('backup.restore-awal') }}"
                class="text-xs mono text-gray-600 hover:text-gray-400 transition">
                📥 Restore dari backup
            </a>
        </div>

        <div class="text-center text-xs text-gray-700 mono mt-5">
            NetMonitor © {{ date('Y') }}
        </div>
    </div>

</body>

</html>