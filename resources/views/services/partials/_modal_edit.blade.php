<div id="modal-edit"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md fadein">
        <div class="mono font-semibold mb-1">✎ Edit Service</div>
        <div class="text-xs text-gray-500 mb-5">Update data service</div>
        <form id="form-edit" method="POST">
            @csrf @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Nama Service</label>
                    <input id="edit-name" name="name" required
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">URL / IP</label>
                    <input id="edit-url" name="url" required
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
                        <input id="edit-cat" name="category"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Departemen</label>
                        <input id="edit-dept" name="department"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Auth Type</label>
                    <select id="edit-auth-type" name="auth_type" onchange="toggleAuthValue('edit')"
                        class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none">
                        <option value="none">Tidak ada</option>
                        <option value="bearer">Bearer Token</option>
                        <option value="basic">Basic Auth</option>
                    </select>
                </div>
                <div id="edit-auth-value-wrap" class="hidden">
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1"
                        id="edit-auth-label">Token</label>
                    <input name="auth_value" id="edit-auth-value"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition"
                        placeholder="">
                    <div class="text-xs text-gray-600 mono mt-1" id="edit-auth-hint"></div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold bg-blue-500 hover:bg-blue-400 text-white rounded-lg transition">Update</button>
            </div>
        </form>
    </div>
</div>