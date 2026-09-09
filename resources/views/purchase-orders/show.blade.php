@php
    $statusColor = match($purchaseOrder->status) {
        'draft' => 'slate',
        'sent' => 'sky',
        'partially_received' => 'amber',
        'received' => 'emerald',
        'cancelled' => 'red',
    };
@endphp

<x-layouts.admin :title="$purchaseOrder->po_number" subtitle="Purchase order details">
    <x-slot:headerActions>
        <x-eva.badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}</x-eva.badge>
    </x-slot:headerActions>

    <div class="space-y-6">
        <x-eva.card title="Order Details">
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-slate-500">Supplier</p>
                    <p class="font-medium text-slate-900">{{ $purchaseOrder->supplier->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Destination Warehouse</p>
                    <p class="font-medium text-slate-900">{{ $purchaseOrder->warehouse->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Expected Date</p>
                    <p class="font-medium text-slate-900">{{ optional($purchaseOrder->expected_date)->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Created By</p>
                    <p class="font-medium text-slate-900">{{ $purchaseOrder->createdBy->name ?? '—' }}</p>
                </div>
            </div>

            <div class="mt-5 flex gap-2">
                @if ($purchaseOrder->status === 'draft')
                    <x-eva.button variant="secondary" :href="route('purchase-orders.edit', $purchaseOrder)">Edit</x-eva.button>
                    <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}">
                        @csrf
                        <x-eva.button type="submit">Send to Supplier</x-eva.button>
                    </form>
                @endif

                @if (in_array($purchaseOrder->status, ['sent', 'partially_received']))
                    <x-eva.button :href="route('purchase-orders.receive', $purchaseOrder)">Receive Goods</x-eva.button>
                @endif

                @if (in_array($purchaseOrder->status, ['draft', 'sent']))
                    <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this purchase order?');">
                        @csrf
                        <x-eva.button type="submit" variant="danger">Cancel PO</x-eva.button>
                    </form>
                @endif
            </div>
        </x-eva.card>

        <x-eva.card title="Line Items">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">SKU</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Ordered</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Received</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Unit Cost (TZS)</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Subtotal (TZS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $item->productVariant->product->name }}</td>
                                <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $item->productVariant->sku }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($item->quantity_ordered) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <span class="{{ $item->quantity_received >= $item->quantity_ordered ? 'text-emerald-600' : ($item->quantity_received > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                                        {{ number_format($item->quantity_received) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">{{ number_format((float) $item->unit_cost, 0) }}</td>
                                <td class="px-3 py-2 text-right font-medium">{{ number_format((float) $item->subtotal, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="px-3 py-3 text-right font-medium text-slate-600">Total</td>
                            <td class="px-3 py-3 text-right font-semibold text-slate-900">{{ number_format($purchaseOrder->totalAmount, 0) }} TZS</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-eva.card>
    </div>
</x-layouts.admin>
