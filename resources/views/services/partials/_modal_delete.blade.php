<div id="modal-delete" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-sm fadein">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-900/40 border border-red-800/50 flex items-center justify-center text-red-400 text-lg flex-shrink-0">✕</div>
            <div>
                <div class="mono font-semibold text-sm">Hapus Service</div>
                <div class="text-xs text-gray-500 mt-0.5">Tindakan ini tidak bisa dibatalkan</div>
            </div>
        </div>

        <div class="bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 mb-5">
            <div class="text-xs text-gray-500 mono mb-0.5">Service yang akan dihapus:</div>
            <div class="font-semibold text-sm text-white" id="delete-name">—</div>
            <div class="text-xs mono text-gray-500 mt-0.5" id="delete-url">—</div>
        </div>

        <form id="form-delete" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold bg-red-500 hover:bg-red-400 text-white rounded-lg transition">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>