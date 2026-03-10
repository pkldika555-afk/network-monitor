<div id="modal-add" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md fadein">
        <div class="mono font-semibold mb-1">+ Tambah Service</div>
        <div class="text-xs text-gray-500 mb-5">Daftarkan IP atau URL internal untuk dipantau</div>
        <form method="POST" action="{{ route('services.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Nama Service</label>
                    <input name="name" required placeholder="Camera Lobby, Timesheet App..."
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">URL / IP</label>
                    <input name="url" required placeholder="http://192.168.100.51/timesheet/"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
                        <input name="category" placeholder="Web App / IP Camera..." list="cat-suggestions"
                            autocomplete="off"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                        <datalist id="cat-suggestions">
                            @foreach($catList as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Departemen</label>
                        <input name="department" placeholder="HRD / IT / Produksi" list="dept-suggestions"
                            autocomplete="off"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                        <datalist id="dept-suggestions">
                            @foreach($depList as $dept)
                                <option value="{{ $dept }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Auth Type</label>
                    <select name="auth_type" id="add-auth-type" onchange="toggleAuthValue('add')"
                        class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none">
                        <option value="none">Tidak ada</option>
                        <option value="bearer">Bearer Token</option>
                        <option value="basic">Basic Auth</option>
                    </select>
                </div>

                <div id="add-auth-value-wrap" class="hidden">
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1"
                        id="add-auth-label">Token</label>
                    <input name="auth_value" id="add-auth-value"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition"
                        placeholder="">
                    <div class="text-xs text-gray-600 mono mt-1" id="add-auth-hint"></div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold bg-green-500 hover:bg-green-400 text-black rounded-lg transition">Simpan</button>
            </div>
        </form>
    </div>
</div>