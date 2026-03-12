<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>History Log — NetMonitor</title>
    @vite(['resources/css/app.css', 'resources/css/monitor.css'])
    <style>
        .filter-select {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 11px;
            font-family: ui-monospace, monospace;
            color: #9ca3af;
            outline: none;
            transition: border-color .15s, color .15s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%234b5563'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 28px;
        }

        .filter-select:focus {
            border-color: #22c55e;
            color: #e5e7eb;
        }

        .log-table thead tr {
            background: #0d1117;
        }

        .log-table tbody tr {
            transition: background .12s;
        }

        .log-table tbody tr:hover {
            background: rgba(255, 255, 255, .025);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 10px;
            font-family: ui-monospace, monospace;
            border-width: 1px;
            border-style: solid;
        }

        .badge-online {
            background: rgba(20, 83, 45, .35);
            color: #4ade80;
            border-color: rgba(74, 222, 128, .2);
        }

        .badge-offline {
            background: rgba(127, 29, 29, .35);
            color: #f87171;
            border-color: rgba(248, 113, 113, .2);
        }

        .badge-unknown {
            background: rgba(31, 41, 55, .6);
            color: #6b7280;
            border-color: rgba(107, 114, 128, .2);
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .page-btn {
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 11px;
            font-family: ui-monospace, monospace;
            background: #111827;
            border: 1px solid #1f2937;
            color: #6b7280;
            transition: all .15s;
        }

        .page-btn:not([disabled]):hover {
            background: #1f2937;
            color: #d1d5db;
        }

        .page-btn[disabled] {
            opacity: .4;
            cursor: default;
        }
    </style>
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

    <div class="max-w-screen-xl mx-auto px-6 py-7">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-base font-bold mono tracking-tight">History Log</h1>
                <p class="text-[11px] text-gray-500 mono mt-0.5">Riwayat seluruh ping &amp; pengecekan service</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] mono text-gray-600 bg-gray-900 border border-gray-800 rounded-lg px-3 py-1.5">
                    Total Log: <span class="text-gray-300">{{ number_format($logs->total()) }}</span>
                    @if(cache('logs_last_reset'))
                        <span class="text-gray-700 mx-1.5">|</span>
                        Terakhir di-reset:
                        <span class="text-gray-400">
                            {{ \Carbon\Carbon::parse(cache('logs_last_reset'))->diffForHumans() }}
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route('logs.index') }}"
            class="flex flex-wrap items-center gap-2 mb-5 p-3 bg-gray-900/60 border border-gray-800 rounded-xl">

            <select name="service_id" class="filter-select">
                <option value="">Semua Service</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
            </select>

            <div class="w-px h-5 bg-gray-800 mx-1 self-center"></div>

            <select name="day" class="filter-select">
                <option value="">Hari</option>
                @for ($i = 1; $i <= 31; $i++)
                    <option value="{{ sprintf('%02d', $i) }}" {{ request('day') == sprintf('%02d', $i) ? 'selected' : '' }}>
                        {{ sprintf('%02d', $i) }}
                    </option>
                @endfor
            </select>

            <select name="month" class="filter-select">
                <option value="">Bulan</option>
                @foreach(['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $num => $name)
                    <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>

            <select name="year" class="filter-select">
                <option value="">Tahun</option>
                @php $currentYear = date('Y'); @endphp
                @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <div class="ml-auto flex items-center gap-2">
                @if(request('service_id') || request('status') || request('day') || request('month') || request('year'))
                    <a href="{{ route('logs.index') }}"
                        class="px-3 py-1.5 text-[11px] mono text-gray-500 hover:text-red-400 border border-gray-800 hover:border-red-900 rounded-lg transition">
                        × Reset
                    </a>
                @endif
                <button type="submit"
                    class="px-4 py-1.5 bg-green-600 hover:bg-green-500 text-black font-bold rounded-lg text-[11px] mono transition shadow-[0_0_12px_rgba(34,197,94,.25)]">
                    Apply Filter
                </button>

                <button type="button" onclick="document.getElementById('modal-reset-logs').classList.remove('hidden')"
                    class="px-3 py-1.5 text-[11px] mono text-red-500 hover:text-red-400 hover:bg-red-900/20 border border-red-900/40 hover:border-red-800 rounded-lg transition">
                    🗑 Reset Logs
                </button>
            </div>
        </form>

        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden log-table">
            <table class="w-full text-[11px] mono">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-500 uppercase tracking-wider text-[10px]">
                        <th class="text-left px-4 py-3 font-medium">Waktu</th>
                        <th class="text-left px-4 py-3 font-medium">Service</th>
                        <th class="text-left px-4 py-3 font-medium">Status</th>
                        <th class="text-left px-4 py-3 font-medium">Response</th>
                        <th class="text-left px-4 py-3 font-medium">HTTP</th>
                        <th class="text-left px-4 py-3 font-medium">Triggered By</th>
                        <th class="text-left px-4 py-3 font-medium">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap tabular-nums">
                                {{ \Carbon\Carbon::parse($log->checked_at)->format('d/m/y H:i:s') }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-gray-200 font-semibold">{{ $log->service->name ?? '—' }}</span>
                                @if($log->service)
                                    <div class="text-gray-600 text-[10px] mt-0.5 truncate max-w-[200px]">
                                        {{ $log->service->url }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if($log->status === 'online')
                                    <span class="badge badge-online">
                                        <span class="badge-dot" style="background:#4ade80"></span> online
                                    </span>
                                @elseif($log->status === 'offline')
                                    <span class="badge badge-offline">
                                        <span class="badge-dot" style="background:#f87171"></span> offline
                                    </span>
                                @else
                                    <span class="badge badge-unknown">
                                        <span class="badge-dot" style="background:#6b7280"></span> unknown
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 tabular-nums">
                                @if($log->response_ms)
                                    <span class="{{ $log->response_ms > 500 ? 'text-yellow-400' : 'text-gray-300' }}">
                                        {{ $log->response_ms }}<span class="text-gray-600 text-[10px]">ms</span>
                                    </span>
                                @else
                                    <span class="text-gray-700">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 tabular-nums">
                                @if($log->http_code)
                                    <span class="{{ $log->http_code >= 400 ? 'text-red-400' : 'text-gray-400' }} font-medium">
                                        {{ $log->http_code }}
                                    </span>
                                @else
                                    <span class="text-gray-700">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                {{ $log->triggered_by ?? '—' }}
                            </td>

                            <td class="px-4 py-3 text-red-400/60 max-w-[220px] truncate" title="{{ $log->error_message }}">
                                {{ $log->error_message ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="text-gray-700 text-2xl mb-2">⌀</div>
                                <p class="text-gray-600 text-[11px]">Belum ada data log</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 flex items-center justify-between text-[11px] mono text-gray-600">
                <span>Halaman <span class="text-gray-400">{{ $logs->currentPage() }}</span> dari <span
                        class="text-gray-400">{{ $logs->lastPage() }}</span></span>
                <div class="flex gap-1.5">
                    @if($logs->onFirstPage())
                        <button disabled class="page-btn">← Prev</button>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="page-btn">← Prev</a>
                    @endif

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="page-btn">Next →</a>
                    @else
                        <button disabled class="page-btn">Next →</button>
                    @endif
                </div>
            </div>
        @endif
        @include('logs.partials._modal-warning', [
            'id' => 'modal-reset-logs',
            'title' => 'Reset Semua Log',
            'subtitle' => 'Seluruh riwayat log akan dihapus permanen',
            'action' => route('logs.reset'),
            'method' => 'POST',
            'confirmText' => 'Ya, Reset',
        ])
    </div>
</body>
</html>