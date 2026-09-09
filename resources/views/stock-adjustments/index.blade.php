<x-layouts.admin title="Stock Adjustments" subtitle="Corrections for damage, loss, expiry, breakage and count discrepancies">
    <x-eva.table-card :paginator="$adjustments" empty="No stock adjustments match this filter.">
        <x-slot:actions>
            <x-eva.button :href="route('stock-adjustments.create')">+ New Adjustment</x-eva.button>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" action="{{ route('stock-adjustments.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['pending' => 'Pending Approval', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if (request('status'))
                    <a href="{{ route('stock-adjustments.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reason</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Requested By</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($adjustments as $adjustment)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $adjustment->productVariant->product->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $adjustment->warehouse->name }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="$adjustment->type === 'add' ? 'emerald' : 'red'">{{ ucfirst($adjustment->type) }}</x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm font-medium text-slate-900">{{ number_format($adjustment->quantity) }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $adjustment->requestedBy->name }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="match($adjustment->status) { 'pending' => 'amber', 'approved' => 'emerald', 'rejected' => 'red' }">
                        {{ ucfirst($adjustment->status) }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="font-medium text-emerald-700 hover:text-emerald-900">View</a>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
