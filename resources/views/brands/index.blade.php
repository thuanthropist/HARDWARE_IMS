<x-layouts.admin title="Brands">
    <x-eva.table-card :paginator="$brands">
        <x-slot:actions>
            <x-eva.button :href="route('brands.create')">+ New Brand</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Products</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($brands as $brand)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $brand->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $brand->products_count }}</td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('brands.edit', $brand) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    <x-eva.confirm-delete :action="route('brands.destroy', $brand)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
