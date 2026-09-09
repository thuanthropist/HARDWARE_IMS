<x-layouts.admin title="Orders" subtitle="Review and act on customer orders from the storefront">
    <x-eva.table-card :paginator="$orders" empty="No orders match this filter.">
        <x-slot:filters>
            <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                    @foreach ([
                        'pending_confirmation' => 'Pending Confirmation',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'partially_fulfilled' => 'Partially Fulfilled',
                        'ready_for_pickup' => 'Ready for Pickup',
                        'out_for_delivery' => 'Out for Delivery',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                        'all' => 'All Statuses',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:filters>

        <x-slot:head>
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Order #</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Delivery</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Placed</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"></th>
            </tr>
        </x-slot:head>

        @foreach ($orders as $order)
            <tr>
                <td class="px-5 py-3 text-sm font-medium text-slate-900">{{ $order->order_number }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $order->name }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ ucfirst($order->delivery_method) }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ $order->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right text-sm font-medium text-slate-900">TZS {{ number_format((float) $order->total) }}</td>
                <td class="px-5 py-3 text-sm">
                    <x-eva.badge :color="match($order->status) {
                        'pending_confirmation' => 'amber',
                        'confirmed', 'processing', 'completed' => 'emerald',
                        'partially_fulfilled' => 'sky',
                        'rejected', 'cancelled' => 'red',
                        default => 'slate',
                    }">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </x-eva.badge>
                </td>
                <td class="px-5 py-3 text-right text-sm">
                    <a href="{{ route('orders.show', $order) }}" class="font-medium text-emerald-700 hover:text-emerald-900">View</a>
                </td>
            </tr>
        @endforeach
    </x-eva.table-card>
</x-layouts.admin>
