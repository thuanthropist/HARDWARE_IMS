<x-layouts.admin title="Purchase Orders" subtitle="Order stock from suppliers and track receipt">
    <x-eva.table-card :paginator="$purchaseOrders" empty="No purchase orders match these filters.">
        <x-slot:actions>
            <x-eva.button :href="route('purchase-orders.create')">+ New Purchase Order</x-eva.button>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All statuses</option>
                    @foreach (['draft' => 'Draft', 'sent' => 'Sent', 'partially_received' => 'Partially Received', 'received' => 'Received', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="supplier_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800">Filter</button>
                @if (request()->hasAny(['status', 'supplier_id']))
                    <a href="{{ route('purchase-orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">PO Number</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Supplier</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Items</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Expected</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($purchaseOrders as $po)
            <tr>
                <td class="px-5 py-3 text-sm font-mono font-medium text-slate-900">{{ $po->po_number }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $po->supplier->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $po->warehouse->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $po->items_count }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ optional($po->expected_date)->format('d M Y') ?? '—' }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="match($po->status) {
                        'draft' => 'slate',
                        'sent' => 'sky',
                        'partially_received' => 'amber',
                        'received' => 'emerald',
                        'cancelled' => 'red',
                    }">
                        {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('purchase-orders.show', $po) }}" class="font-medium text-emerald-700 hover:text-emerald-900">View</a>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
