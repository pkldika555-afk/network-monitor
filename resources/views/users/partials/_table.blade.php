<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="text-left px-4 py-3">Nama</th>
                        <th class="text-left px-4 py-3">NRP</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-left px-4 py-3">Role</th>
                        <th class="text-left px-4 py-3">Dibuat</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="border-b border-gray-800/50 hover:bg-gray-800/30 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-medium">{{ $user->name }}</span>
                                @if($user->id === auth()->id())
                                    <span class="text-xs mono px-1.5 py-0.5 rounded bg-blue-900/30 text-blue-400 border border-blue-800/40">you</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 mono text-gray-400 text-xs">{{ $user->nrp ?? '—' }}</td>
                        <td class="px-4 py-3 mono text-gray-400 text-xs">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if($user->role === 'admin')
                                <span class="text-xs mono px-2 py-0.5 rounded bg-green-900/30 text-green-400 border border-green-800/40">admin</span>
                            @else
                                <span class="text-xs mono px-2 py-0.5 rounded bg-gray-800 text-gray-500 border border-gray-700">{{ $user->role }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 mono text-gray-600 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="openEditUser(this)"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-nrp="{{ $user->nrp }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}"
                                    class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-gray-700 text-gray-500 hover:text-white text-xs transition">✎
                                </button>
                                @if($user->id !== auth()->id())
                                <button onclick="openDeleteUser(this)"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    class="w-7 h-7 flex items-center justify-center rounded border border-gray-700 hover:bg-red-900/30 hover:border-red-800 text-gray-500 hover:text-red-400 text-xs transition">✕
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-gray-600 text-sm">Belum ada user.</td>
                    </tr>
                @endforelse
                @include('users.partials._modal_delete')
            </tbody>
        </table>
    </div>
</div>
