<div id="modal-edit-user"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md">
        <div class="mono font-semibold mb-1">✎ Edit User</div>
        <div class="text-xs text-gray-500 mb-5">Update data user. Kosongkan password jika tidak ingin mengubah.</div>
        <form id="form-edit-user" method="POST">
            @csrf @method('PUT')
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Nama</label>
                    <input id="eu-name" name="name" required
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">NRP</label>
                        <input id="eu-nrp" name="nrp"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Role</label>
                        <select id="eu-role" name="role"
                            class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Email</label>
                    <input id="eu-email" name="email" type="email" required
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Password Baru <span
                            class="text-gray-600">(opsional)</span></label>
                    <input id="eu-password" name="password" type="password" placeholder="••••••••"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Konfirmasi Password</label>
                    <input name="password_confirmation" type="password" placeholder="••••••••"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')"
                    class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold bg-blue-500 hover:bg-blue-400 text-white rounded-lg transition">Update</button>
            </div>
        </form>
    </div>
</div>