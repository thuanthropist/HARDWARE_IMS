<x-layouts.admin title="Staff" subtitle="Manage admin panel access and roles">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'users'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    <div class="mb-5 flex flex-col gap-4 rounded-xl bg-emerald-900 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('users.index') }}" class="flex-1 sm:max-w-sm">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M19 11a8 8 0 11-16 0 8 8 0 0116 0z" /></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search staff by name or email"
                    class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-amber-400">
            </div>
        </form>
        <p class="text-sm font-medium text-emerald-100">{{ $users->total() }} {{ Str::plural('staff member', $users->total()) }}</p>
    </div>

    <x-eva.table-card :paginator="$users" empty="No staff members found.">
        <x-slot:actions>
            <x-eva.button variant="accent" :href="route('users.create')">+ New Staff</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Staff</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Department</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($users as $user)
            <tr>
                <td class="px-5 py-3">
                    <a href="{{ route('users.show', $user) }}" class="flex items-center gap-3">
                        @if ($user->avatar_path)
                            <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover">
                        @else
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-700">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <span>
                            <span class="block text-sm font-medium text-slate-900 hover:text-emerald-700">{{ $user->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $user->email }}</span>
                        </span>
                    </a>
                </td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge color="amber">{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</x-eva.badge>
                </td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $user->department->name ?? 'All departments' }}</td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('users.show', $user) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">View</a>
                    <a href="{{ route('users.edit', $user) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    @if ($user->id !== auth()->id())
                        <x-eva.confirm-delete :action="route('users.destroy', $user)" />
                    @endif
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
