<x-layouts.admin title="Stock Transfers" subtitle="Move stock between department-specialized warehouses">
    <x-eva.table-card :paginator="$transfers" empty="No stock transfers match this filter.">
        <x-slot:actions>
            <x-eva.button :href="route('stock-transfers.create')">+ New Transfer</x-eva.button>
        </x-slot:actions>

        <x-slot:filters>
            <form method="GET" action="{{ route('stock-transfers.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['pending' => 'Pending', 'in_transit' => 'In Transit', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if (request('status'))
                    <a href="{{ route('stock-transfers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Transfer #</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">From</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">To</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Items</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($transfers as $transfer)
            <tr>
                <td class="px-5 py-3 text-sm font-mono font-medium text-slate-900">{{ $transfer->transfer_number }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $transfer->fromWarehouse->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $transfer->toWarehouse->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $transfer->items_count }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="match($transfer->status) {
                        'pending' => 'slate',
                        'in_transit' => 'amber',
                        'completed' => 'emerald',
                        'cancelled' => 'red',
                    }">
                        {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('stock-transfers.show', $transfer) }}" class="font-medium text-emerald-700 hover:text-emerald-900">View</a>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
