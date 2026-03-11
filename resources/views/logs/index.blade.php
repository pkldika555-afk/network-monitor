<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>History Log — NetMonitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css'])
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-950 text-gray-100 min-h-screen">

    <nav class="bg-gray-900 border-b border-gray-800 h-14 flex items-center px-6 gap-4 sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-green-500 rounded-md flex items-center justify-center text-black font-bold text-xs mono">NM</div>
            <span class="font-bold text-sm mono">NetMonitor</span>
        </div>
        <div class="w-px h-5 bg-gray-700"></div>
        <span class="text-xs mono text-gray-500">History Log</span>

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

            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold mono">History Log</h1>
                    <p class="text-xs text-gray-500 mono mt-0.5">Riwayat seluruh ping & pengecekan service</p>
                </div>
                <span class="text-xs mono text-gray-600">Total: {{ $logs->total() }} entri</span>
            </div>

            <form method="GET" action="{{ route('logs.index') }}" class="flex items-center gap-2 mb-5">
                <select name="service_id"
                    class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-xs mono text-gray-400 outline-none">
                    <option value="">Semua Service</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status"
                    class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-xs mono text-gray-400 outline-none">
                    <option value="">Semua Status</option>
                    <option value="online"  {{ request('status') == 'online'  ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                </select>

                <button type="submit"
                    class="px-3 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-xs mono text-gray-300 transition">
                    Filter
                </button>

                @if(request('service_id') || request('status'))
                    <a href="{{ route('logs.index') }}"
                        class="px-3 py-2 text-xs mono text-gray-500 hover:text-gray-300 transition">
                        Reset
                    </a>
                @endif
            </form>

            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                <table class="w-full text-xs mono">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-500">
                            <th class="text-left px-4 py-3 font-medium">Waktu</th>
                            <th class="text-left px-4 py-3 font-medium">Service</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Response</th>
                            <th class="text-left px-4 py-3 font-medium">HTTP</th>
                            <th class="text-left px-4 py-3 font-medium">Triggered By</th>
                            <th class="text-left px-4 py-3 font-medium">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="border-b border-gray-800/50 hover:bg-gray-800/30 transition">

                                <td class="px-4 py-3 text-gray-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log->checked_at)->format('d/m/y H:i:s') }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="text-gray-200 font-medium">{{ $log->service->name ?? '—' }}</span>
                                    @if($log->service)
                                        <div class="text-gray-600 text-[10px] mt-0.5">{{ $log->service->url }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($log->status === 'online')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-900/40 text-green-400 border border-green-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span> online
                                        </span>
                                    @elseif($log->status === 'offline')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-900/40 text-red-400 border border-red-800/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span> offline
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-800 text-gray-500 border border-gray-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500 inline-block"></span> unknown
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($log->response_ms)
                                        <span class="{{ $log->response_ms > 500 ? 'text-yellow-400' : 'text-gray-300' }}">
                                            {{ $log->response_ms }}ms
                                        </span>
                                    @else
                                        <span class="text-gray-700">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($log->http_code)
                                        <span class="{{ $log->http_code >= 400 ? 'text-red-400' : 'text-gray-400' }}">
                                            {{ $log->http_code }}
                                        </span>
                                    @else
                                        <span class="text-gray-700">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-gray-500">
                                    {{ $log->triggered_by ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-red-400/70 max-w-xs truncate">
                                    {{ $log->error_message ?? '' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-600">
                                    Belum ada data log
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="mt-4 flex items-center justify-between text-xs mono text-gray-600">
                    <span>Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
                    <div class="flex gap-1">
                        @if($logs->onFirstPage())
                            <span class="px-3 py-1.5 bg-gray-900 border border-gray-800 rounded-lg text-gray-700">← Prev</span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded-lg hover:bg-gray-800 text-gray-400 transition">← Prev</a>
                        @endif

                        @if($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded-lg hover:bg-gray-800 text-gray-400 transition">Next →</a>
                        @else
                            <span class="px-3 py-1.5 bg-gray-900 border border-gray-800 rounded-lg text-gray-700">Next →</span>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</body>
</html>