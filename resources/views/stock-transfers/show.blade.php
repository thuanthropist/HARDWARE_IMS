@php
    $statusColor = match($transfer->status) {
        'pending' => 'slate',
        'in_transit' => 'amber',
        'completed' => 'emerald',
        'cancelled' => 'red',
    };
@endphp

<x-layouts.admin :title="$transfer->transfer_number" subtitle="Stock transfer details">
    <x-slot:headerActions>
        <x-eva.badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</x-eva.badge>
    </x-slot:headerActions>

    <div class="space-y-6">
        <x-eva.card title="Transfer Details">
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-slate-500">From</p>
                    <p class="font-medium text-slate-900">{{ $transfer->fromWarehouse->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">To</p>
                    <p class="font-medium text-slate-900">{{ $transfer->toWarehouse->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Initiated By</p>
                    <p class="font-medium text-slate-900">{{ $transfer->initiatedBy->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500">Received By</p>
                    <p class="font-medium text-slate-900">{{ $transfer->receivedBy->name ?? '—' }}</p>
                </div>
            </div>

            <div class="mt-5 flex gap-2">
                @if ($transfer->status === 'pending')
                    <form method="POST" action="{{ route('stock-transfers.dispatch', $transfer) }}">
                        @csrf
                        <x-eva.button type="submit">Dispatch (Deduct from Source)</x-eva.button>
                    </form>
                @endif

                @if ($transfer->status === 'in_transit')
                    <form method="POST" action="{{ route('stock-transfers.receive', $transfer) }}">
                        @csrf
                        <x-eva.button type="submit">Confirm Receipt at Destination</x-eva.button>
                    </form>
                @endif

                @if (in_array($transfer->status, ['pending', 'in_transit']))
                    <form method="POST" action="{{ route('stock-transfers.cancel', $transfer) }}" onsubmit="return confirm('Cancel this transfer? Any dispatched stock will be returned to the source warehouse.');">
                        @csrf
                        <x-eva.button type="submit" variant="danger">Cancel Transfer</x-eva.button>
                    </form>
                @endif
            </div>
        </x-eva.card>

        <x-eva.card title="Items">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">SKU</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transfer->items as $item)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $item->productVariant->product->name }}</td>
                                <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $item->productVariant->sku }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-eva.card>
    </div>
</x-layouts.admin>
