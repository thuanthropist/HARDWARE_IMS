<x-layouts.admin title="Departments" subtitle="Manage store departments and their attribute schemas">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('settings.index', ['tab' => 'departments'])">&larr; Back to Settings</x-eva.button>
    </x-slot:headerActions>

    <x-eva.table-card :paginator="$departments">
        <x-slot:actions>
            <x-eva.button :href="route('departments.create')">+ New Department</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Categories</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Products</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($departments as $department)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $department->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $department->categories_count }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $department->products_count }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="$department->is_active ? 'emerald' : 'slate'">
                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('departments.edit', $department) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    <x-eva.confirm-delete :action="route('departments.destroy', $department)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
