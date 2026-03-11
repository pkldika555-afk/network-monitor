<div id="modal-add-user"
    class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md">
        <div class="mono font-semibold mb-1">+ Tambah User</div>
        <div class="text-xs text-gray-500 mb-5">Daftarkan user baru</div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Nama</label>
                    <input name="name" required placeholder="John Doe"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">NRP</label>
                        <input name="nrp" placeholder="12345"
                            class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Role</label>
                        <select name="role"
                            class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Email</label>
                    <input name="email" type="email" required placeholder="john@netmonitor.local"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Password</label>
                    <input name="password" type="password" required placeholder="••••••••"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Konfirmasi Password</label>
                    <input name="password_confirmation" type="password" required placeholder="••••••••"
                        class="w-full bg-gray-950 border border-gray-700 focus:border-blue-500 rounded-lg px-3 py-2 text-sm mono text-gray-200 outline-none transition">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')"
                    class="px-4 py-2 text-xs text-gray-400 border border-gray-700 rounded-lg hover:text-white transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-bold bg-green-500 hover:bg-green-400 text-black rounded-lg transition">Simpan</button>
            </div>
        </form>
    </div>
</div>