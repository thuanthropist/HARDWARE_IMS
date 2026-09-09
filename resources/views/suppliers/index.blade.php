<x-layouts.admin title="Suppliers">
    <x-eva.table-card :paginator="$suppliers">
        <x-slot:actions>
            <x-eva.button :href="route('suppliers.create')">+ New Supplier</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Contact</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Departments</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($suppliers as $supplier)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $supplier->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $supplier->contact_person }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $supplier->phone }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $supplier->email }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">
                    <div class="flex flex-wrap gap-1">
                        @forelse ($supplier->departments as $department)
                            <x-eva.badge color="emerald">{{ $department->name }}</x-eva.badge>
                        @empty
                            <span class="text-slate-400">All departments</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="$supplier->is_active ? 'emerald' : 'slate'">
                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    <x-eva.confirm-delete :action="route('suppliers.destroy', $supplier)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
