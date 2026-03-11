<div id="{{ $id }}"
     class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     onclick="if(event.target===this)this.classList.add('hidden')">

    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-sm">

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-900/40 border border-red-800/50 flex items-center justify-center text-red-400 text-lg flex-shrink-0">
                ⚠
            </div>
            <div>
                <div class="mono font-semibold text-sm">{{ $title }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $subtitle ?? 'Tindakan ini tidak bisa dibatalkan' }}</div>
            </div>
        </div>

        @isset($info)
            <div class="bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 mb-5">
                {{ $info }}
            </div>
        @endisset

        <div class="flex justify-end gap-2">
            <button type="button"
                onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">
                Batal
            </button>

            <form method="POST" action="{{ $action }}">
                @csrf
                @if(isset($method) && strtoupper($method) !== 'POST')
                    @method($method)
                @endif
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold bg-red-500 hover:bg-red-400 text-white rounded-lg transition">
                    {{ $confirmText ?? 'Konfirmasi' }}
                </button>
            </form>
        </div>

    </div>
</div>
