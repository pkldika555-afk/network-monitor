<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3" id="cards-grid">
    @forelse($services as $svc)
        <div class="service-card bg-gray-900 border border-gray-800 rounded-xl overflow-hidden card-{{ $svc->status }} fadein"
            data-name="{{ strtolower($svc->name) }}" data-url="{{ strtolower($svc->url) }}"
            data-dept="{{ strtolower($svc->department ?? '') }}" data-cat="{{ $svc->category }}"
            data-status="{{ $svc->status }}" id="card-{{ $svc->id }}">

            <div class="p-4 pb-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-bold text-sm truncate">{{ $svc->name }}</div>
                        <div class="mono text-xs text-gray-500 mt-0.5 truncate">{{ $svc->url }}</div>
                    </div>
                    <div id="badge-{{ $svc->id }}" class="flex-shrink-0 mt-0.5">
                        @include('services._badge', ['status' => $svc->status])
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 mt-3">
                    <span
                        class="text-xs mono px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-800/40">{{ $svc->category }}</span>
                    @if($svc->department)
                        <span
                            class="text-xs mono px-2 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-800/40">{{ $svc->department }}</span>
                    @endif
                    <span id="ms-{{ $svc->id }}"
                        class="text-xs mono px-2 py-0.5 rounded
                              {{ $svc->response_ms ? ($svc->response_ms < 150 ? 'text-green-400' : ($svc->response_ms < 400 ? 'text-yellow-400' : 'text-red-400')) : 'text-gray-600' }}">
                        ⚡ {{ $svc->response_ms ? $svc->response_ms . 'ms' : '—ms' }}
                    </span>
                    @if($svc->auth_type && $svc->auth_type !== 'none')
                        <span class="text-xs mono px-2 py-0.5 rounded bg-gray-800 text-gray-500">🔒
                            {{ $svc->auth_type }}</span>
                    @endif
                </div>
            </div>

            <div class="px-4 py-2 border-t border-gray-800 flex items-center justify-between">
                <div id="lastcheck-{{ $svc->id }}" class="text-xs mono text-gray-600">
                    {{ $svc->last_checked_at ? \Carbon\Carbon::parse($svc->last_checked_at)->diffForHumans() : 'Belum dicek' }}
                </div>
                <div class="flex items-center gap-2">
                    @if($svc->assigned_to)
                        <div
                            class="w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($svc->assigned_to, 0, 1)) }}
                        </div>
                        <span class="text-xs mono text-gray-300" id="assign-{{ $svc->id }}">{{ $svc->assigned_to }}</span>
                    @else
                        <span class="text-xs mono text-gray-600" id="assign-{{ $svc->id }}">— unassigned</span>
                    @endif
                    <button onclick="openAssign({{ $svc->id }}, '{{ $svc->assigned_to }}')"
                        class="text-xs mono text-gray-600 hover:text-blue-400 transition" title="Assign">
                        ◉
                    </button>
                </div>
                <div class="flex items-center gap-1.5">

                    <button onclick="checkSingle({{ $svc->id }})" id="btn-{{ $svc->id }}"
                        class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-gray-700 text-gray-500 hover:text-white text-xs transition"
                        title="Cek">
                        <span id="icon-{{ $svc->id }}">⟳</span>
                    </button>
                    <button
                        onclick="openEdit({{ $svc->id }}, '{{ addslashes($svc->name) }}', '{{ addslashes($svc->url) }}', '{{ $svc->category }}', '{{ $svc->department }}', '{{ $svc->auth_type }}')"
                        class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-gray-700 text-gray-500 hover:text-white text-xs transition"
                        title="Edit">✎
                    </button>
                    <form method="POST" action="{{ route('services.destroy', $svc->id) }}"
                        onsubmit="return confirm('Hapus {{ $svc->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-red-900/30 hover:border-red-800 text-gray-500 hover:text-red-400 text-xs transition"
                            title="Hapus">✕
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-20 text-gray-700">
            <div class="text-4xl mb-3">📡</div>
            <p class="text-sm">Belum ada service. Klik "+ Tambah" untuk mulai.</p>
        </div>
    @endforelse
</div>