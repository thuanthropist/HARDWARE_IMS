<x-layouts.admin title="Warehouses">
    <x-eva.table-card :paginator="$warehouses">
        <x-slot:actions>
            <x-eva.button :href="route('warehouses.create')">+ New Warehouse</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Location</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Stock Lines</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($warehouses as $warehouse)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $warehouse->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $warehouse->location }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $warehouse->stock_levels_count }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="$warehouse->is_active ? 'emerald' : 'slate'">
                        {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    <x-eva.confirm-delete :action="route('warehouses.destroy', $warehouse)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
