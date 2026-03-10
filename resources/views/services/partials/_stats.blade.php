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
                {{ $services->where('status', 'online')->count() }}
            </div>
            <div class="text-xs text-gray-600 mt-0.5">Online</div>
        </div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center text-base">✕</div>
        <div>
            <div class="mono font-semibold text-2xl text-red-400" id="sum-offline">
                {{ $services->where('status', 'offline')->count() }}
            </div>
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