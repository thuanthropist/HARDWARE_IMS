<x-layouts.admin title="POS Sales History" subtitle="All walk-in counter sales">
    <x-slot:headerActions>
        <x-eva.button :href="route('pos.index')">+ New Sale</x-eva.button>
    </x-slot:headerActions>

    <x-eva.table-card :paginator="$sales" empty="No sales match this filter.">
        <x-slot:filters>
            <form method="GET" action="{{ route('pos.sales.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                <span class="text-slate-400 text-sm">to</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">

                <select name="cashier_id" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All cashiers</option>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" @selected(request('cashier_id') == $cashier->id)>{{ $cashier->name }}</option>
                    @endforeach
                </select>

                <select name="department_id" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>

                <select name="payment_method" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All payment methods</option>
                    @foreach (['cash' => 'Cash', 'mobile_money' => 'Mobile Money', 'card' => 'Card'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600">Filter</button>
                @if (request()->anyFilled(['date_from', 'date_to', 'cashier_id', 'department_id', 'payment_method']))
                    <a href="{{ route('pos.sales.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
                @endif
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Sale #</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Cashier</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Warehouse</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Payment</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($sales as $sale)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $sale->sale_number }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $sale->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $sale->cashier->name ?? '—' }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $sale->warehouse->name }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge color="slate">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm font-medium text-slate-900">TZS {{ number_format((float) $sale->total) }}</td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('pos.sales.show', $sale) }}" class="font-medium text-emerald-700 hover:text-emerald-900">View</a>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
