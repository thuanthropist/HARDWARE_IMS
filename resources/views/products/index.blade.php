<x-layouts.admin title="Products" subtitle="Manage inventory across all departments">
    <x-eva.table-card :tabs="$tabs" :paginator="$products">
        <x-slot:actions>
            <x-eva.button :href="route('products.create')">+ New Product</x-eva.button>
        </x-slot:actions>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">SKU</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Department</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Stock</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Price (TZS)</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
            </tr>
        </x-slot:head>

        @foreach ($products as $product)
            @php $stock = $product->variants->sum(fn ($v) => $v->stockLevels->sum('quantity')); @endphp
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $product->name }}</td>
                <td class="px-5 py-3 text-sm font-mono text-xs text-slate-500">{{ $product->sku }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $product->department->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $product->category->name }}</td>
                <td class="px-5 py-3 text-right text-sm">
                    <span class="{{ $stock <= $product->reorder_point ? 'font-semibold text-amber-600' : 'text-slate-600' }}">
                        {{ number_format($stock) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right text-sm text-slate-600">{{ number_format((float) $product->selling_price, 0) }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="$product->is_active ? 'emerald' : 'slate'">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('products.edit', $product) }}" class="mr-3 font-medium text-emerald-700 hover:text-emerald-900">Edit</a>
                    <x-eva.confirm-delete :action="route('products.destroy', $product)" />
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
