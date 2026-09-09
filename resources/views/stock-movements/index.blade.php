<x-layouts.admin title="Stock Movements" subtitle="Read-only log of every stock in/out/transfer/adjustment">
    <x-eva.table-card :paginator="$movements" empty="No stock movements match these filters.">
        <x-slot:filters>
            <form method="GET" action="{{ route('stock-movements.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product or SKU"
                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">

                <select name="warehouse_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All warehouses</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>

                <select name="type" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All types</option>
                    @foreach (['in' => 'In', 'out' => 'Out', 'transfer' => 'Transfer', 'adjustment' => 'Adjustment'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800">Filter</button>
                @if (request()->hasAny(['search', 'warehouse_id', 'type']))
                    <a href="{{ route('stock-movements.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Quantity</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Performed By</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Note</th>
            </tr>
        </x-slot:head>

        @foreach ($movements as $movement)
            <tr>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $movement->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">
                    {{ $movement->productVariant->product->name }}
                    <span class="block text-xs text-slate-400">{{ $movement->productVariant->sku }}</span>
                </td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $movement->warehouse->name }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="match($movement->type) { 'in' => 'emerald', 'out' => 'red', 'transfer' => 'sky', default => 'amber' }">
                        {{ ucfirst($movement->type) }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm font-medium text-slate-900">{{ number_format($movement->quantity) }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $movement->performedBy->name ?? 'System' }}</td>
                <td class="px-5 py-3 text-sm text-slate-500">{{ $movement->note }}</td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
