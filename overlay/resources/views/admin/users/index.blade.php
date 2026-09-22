<x-admin.layout title="Staff">
    <x-slot:actions>
        @can('users.create')
            <x-admin.button :href="route('admin.users.create')">+ Add staff member</x-admin.button>
        @endcan
    </x-slot:actions>

    <div class="overflow-x-auto rounded-sm border border-paper-300 bg-white">
        <table class="w-full min-w-[640px] text-left text-small">
            <thead class="border-b border-paper-200 bg-paper-50 font-mono text-[11px] uppercase tracking-wider text-paper-500">
                <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Last sign-in</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-paper-200">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-5 py-3">
                            <span class="block font-display text-[15px] font-semibold text-navy-700">{{ $u->name }}</span>
                            <span class="text-xs text-paper-500">{{ $u->email }}</span>
                        </td>
                        <td class="px-5 py-3">{{ $u->roles->pluck('name')->join(', ') ?: 'No role' }}</td>
                        <td class="px-5 py-3 text-paper-600">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="px-5 py-3">{{ $u->is_active ? 'Active' : 'Deactivated' }}</td>
                        <td class="px-5 py-3 text-right">
                            @can('users.update')
                                <a href="{{ route('admin.users.edit', $u) }}" class="font-display text-sm font-semibold text-navy-700 hover:text-lime-700">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 max-w-3xl rounded-sm border border-paper-300 bg-white p-5 text-small text-paper-600">
        <p class="font-display font-semibold text-navy-700">What each role can do</p>
        <ul class="mt-2 grid gap-1">
            <li><strong class="text-navy-700">Super Admin</strong> - everything, including staff accounts and settings.</li>
            <li><strong class="text-navy-700">Inventory Manager</strong> - products and categories.</li>
            <li><strong class="text-navy-700">Content Manager</strong> - pages and portfolio projects.</li>
            <li><strong class="text-navy-700">Sales</strong> - reads and answers messages; sees products.</li>
        </ul>
    </div>
</x-admin.layout>
