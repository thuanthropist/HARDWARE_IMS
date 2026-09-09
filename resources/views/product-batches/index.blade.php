<x-layouts.admin title="Batches & Expiry" subtitle="Batch-tracked stock only — most products don't carry batches">
    <x-eva.table-card :paginator="$batches" empty="No batch-tracked stock on hand.">
        <x-slot:filters>
            <form method="GET" action="{{ route('product-batches.index') }}" class="flex items-center gap-2">
                <label class="flex items-center gap-1.5 text-sm text-slate-600">
                    <input type="checkbox" name="expiring" value="1" @checked(request('expiring')) onchange="this.form.submit()"
                           class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-500">
                    Expiring within {{ config('inventory.expiry_warning_days') }} days only
                </label>
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Batch #</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Quantity</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Received</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Expiry</th>
            </tr>
        </x-slot:head>

        @foreach ($batches as $batch)
            @php
                $daysLeft = $batch->expiry_date ? now()->startOfDay()->diffInDays($batch->expiry_date, false) : null;
            @endphp
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $batch->productVariant->product->name }}</td>
                <td class="px-5 py-3 text-sm font-mono text-xs text-slate-500">{{ $batch->batch_number }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $batch->warehouse->name }}</td>
                <td class="px-5 py-3 text-right text-sm">{{ number_format($batch->quantity) }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $batch->received_date->format('d M Y') }}</td>
                <td class="px-5 py-3 text-sm">
                    @if ($batch->expiry_date)
                        <x-eva.badge :color="$daysLeft <= config('inventory.expiry_warning_days') ? 'amber' : 'slate'">
                            {{ $batch->expiry_date->format('d M Y') }}
                        </x-eva.badge>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
