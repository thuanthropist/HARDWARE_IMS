@php
    $statusColor = match($adjustment->status) { 'pending' => 'amber', 'approved' => 'emerald', 'rejected' => 'red' };
    $attributeSummary = $adjustment->productVariant->product->productAttributes
        ->map(fn ($attr) => $attr->attribute_value . $attr->attribute_unit)
        ->implode(', ');
@endphp

<x-layouts.admin title="Stock Adjustment #{{ $adjustment->id }}" subtitle="Adjustment details and approval trail">
    <x-slot:headerActions>
        <x-eva.badge :color="$statusColor">{{ ucfirst($adjustment->status) }}</x-eva.badge>
    </x-slot:headerActions>

    <x-eva.card>
        <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
            <div>
                <p class="text-slate-500">Product</p>
                <p class="font-medium text-slate-900">{{ $adjustment->productVariant->product->name }}</p>
                @if ($attributeSummary)
                    <p class="text-xs font-medium text-emerald-700">{{ $attributeSummary }}</p>
                @endif
            </div>
            <div>
                <p class="text-slate-500">Warehouse</p>
                <p class="font-medium text-slate-900">{{ $adjustment->warehouse->name }}</p>
            </div>
            <div>
                <p class="text-slate-500">Type / Quantity</p>
                <p class="font-medium text-slate-900">
                    <x-eva.badge :color="$adjustment->type === 'add' ? 'emerald' : 'red'">{{ ucfirst($adjustment->type) }}</x-eva.badge>
                    {{ number_format($adjustment->quantity) }}
                </p>
            </div>
            <div>
                <p class="text-slate-500">Reason</p>
                <p class="font-medium text-slate-900">{{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}</p>
            </div>
            <div>
                <p class="text-slate-500">Requested By</p>
                <p class="font-medium text-slate-900">{{ $adjustment->requestedBy->name }}</p>
            </div>
            @if ($adjustment->approvedBy)
                <div>
                    <p class="text-slate-500">{{ $adjustment->status === 'rejected' ? 'Rejected By' : 'Approved By' }}</p>
                    <p class="font-medium text-slate-900">{{ $adjustment->approvedBy->name }} on {{ $adjustment->approved_at->format('d M Y H:i') }}</p>
                </div>
            @endif
        </div>

        <div class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
            <p class="mb-1 font-medium text-slate-500">Note</p>
            {{ $adjustment->note }}
        </div>

        @if ($adjustment->status === 'pending')
            <div class="mt-5 flex gap-2">
                @can('manage-stock-adjustments')
                    <form method="POST" action="{{ route('stock-adjustments.approve', $adjustment) }}">
                        @csrf
                        <x-eva.button type="submit">Approve &amp; Apply to Stock</x-eva.button>
                    </form>
                    <form method="POST" action="{{ route('stock-adjustments.reject', $adjustment) }}" onsubmit="return confirm('Reject this adjustment?');">
                        @csrf
                        <x-eva.button type="submit" variant="danger">Reject</x-eva.button>
                    </form>
                @else
                    <p class="text-sm text-slate-500">This adjustment exceeds the auto-approval threshold and is awaiting a Manager or Admin.</p>
                @endcan
            </div>
        @endif
    </x-eva.card>
</x-layouts.admin>
