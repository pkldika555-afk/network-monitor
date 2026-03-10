<div id="modal-assign" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
  <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-sm fadein">
    <div class="mono font-semibold mb-1">◉ Assign Service</div>
    <div class="text-xs text-gray-500 mb-5">Tandai IP ini dipakai oleh siapa. Kosongkan untuk unassign.</div>
    <div>
      <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Dipakai oleh</label>
      <input id="assign-input" placeholder="e.g. HRD / Dika / CCTV Lobby..."
        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
    </div>
    <div class="flex justify-end gap-2 mt-5">
      <button onclick="document.getElementById('modal-assign').classList.add('hidden')"
        class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
      <button onclick="saveAssign()"
        class="px-4 py-2 text-xs font-bold bg-blue-500 hover:bg-blue-400 text-white rounded-lg transition">Simpan</button>
    </div>
  </div>
</div>