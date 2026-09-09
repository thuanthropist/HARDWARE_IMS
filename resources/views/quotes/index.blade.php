<x-layouts.admin title="Quotes" subtitle="Review, price and send formal quotes for customer requests">
    <x-eva.table-card :paginator="$quotes" empty="No quotes match this filter.">
        <x-slot:filters>
            <form method="GET" action="{{ route('quotes.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                    @foreach ([
                        'requested' => 'Requested',
                        'reviewed' => 'Reviewed',
                        'sent' => 'Sent',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                        'all' => 'All Statuses',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Quote #</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Source</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Requested</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($quotes as $quote)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $quote->quote_number }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $quote->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ ucfirst($quote->source) }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $quote->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right text-sm font-medium text-slate-900">
                    {{ $quote->total !== null ? 'TZS '.number_format((float) $quote->total) : '—' }}
                </td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="match($quote->status) {
                        'requested' => 'amber',
                        'reviewed' => 'sky',
                        'sent' => 'sky',
                        'accepted' => 'emerald',
                        'rejected', 'expired' => 'red',
                        default => 'slate',
                    }">
                        {{ ucfirst($quote->status) }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('quotes.show', $quote) }}" class="font-medium text-emerald-700 hover:text-emerald-900">View</a>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
