<x-layouts.admin title="Categories" subtitle="Product taxonomy per department">
    <x-eva.table-card :paginator="$categories">
        <x-slot:actions>
            <x-eva.button :href="route('categories.create')">+ New Category</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Department</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Parent</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Products</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($categories as $category)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $category->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $category->department->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $category->parent->name ?? '—' }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $category->products_count }}</td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('categories.edit', $category) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    <x-eva.confirm-delete :action="route('categories.destroy', $category)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
