@php
    $statusColor = match($order->status) {
        'pending_confirmation' => 'amber',
        'confirmed', 'processing', 'completed' => 'emerald',
        'partially_fulfilled' => 'sky',
        'rejected', 'cancelled' => 'red',
        default => 'slate',
    };
@endphp

<x-layouts.admin title="Order {{ $order->order_number }}" subtitle="Placed {{ $order->created_at->format('d M Y H:i') }}">
    <x-slot:headerActions>
        <x-eva.badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</x-eva.badge>
    </x-slot:headerActions>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-eva.card title="Customer">
                <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-slate-500">Name</p>
                        <p class="font-medium text-slate-900">{{ $order->name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Email</p>
                        <p class="font-medium text-slate-900">{{ $order->email }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Phone</p>
                        <p class="font-medium text-slate-900">{{ $order->phone }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Delivery Method</p>
                        <p class="font-medium text-slate-900">{{ ucfirst($order->delivery_method) }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-slate-500">{{ $order->delivery_method === 'pickup' ? 'Pickup Warehouse' : 'Delivery Address' }}</p>
                        <p class="font-medium text-slate-900">{{ $order->delivery_method === 'pickup' ? ($order->warehouse->name ?? 'Not yet assigned') : $order->delivery_address }}</p>
                    </div>
                </div>
            </x-eva.card>

            @foreach ($groups as $label => $items)
                <x-eva.card :title="$label">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="py-2">Item</th>
                                    <th class="py-2 text-right">Ordered</th>
                                    <th class="py-2 text-right">Available</th>
                                    <th class="py-2 text-right">Unit Price</th>
                                    <th class="py-2 text-right">Line Total</th>
                                    <th class="py-2 text-left">Fulfillment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($items as $item)
                                    @php $available = $stockByLine[$item->id] ?? 0; @endphp
                                    <tr>
                                        <td class="py-2 font-medium text-slate-900">{{ $item->product_name }}</td>
                                        <td class="py-2 text-right">{{ $item->quantity }}</td>
                                        <td class="py-2 text-right {{ $item->product_id && $available < $item->quantity ? 'font-semibold text-red-600' : 'text-slate-600' }}">
                                            {{ $item->product_id ? $available : '—' }}
                                        </td>
                                        <td class="py-2 text-right">{{ number_format((float) $item->unit_price) }}</td>
                                        <td class="py-2 text-right font-medium">{{ number_format((float) $item->line_total) }}</td>
                                        <td class="py-2">
                                            <x-eva.badge :color="match($item->fulfillment_status) {
                                                'fulfilled' => 'emerald',
                                                'backordered' => 'amber',
                                                'dropped' => 'red',
                                                default => 'slate',
                                            }">
                                                {{ ucfirst($item->fulfillment_status) }}
                                                @if ($item->fulfillment_status !== 'pending')
                                                    ({{ $item->fulfilled_quantity }}/{{ $item->quantity }})
                                                @endif
                                            </x-eva.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-eva.card>
            @endforeach

            <x-eva.card title="Status History">
                <ol class="space-y-3">
                    @forelse ($order->statusHistory as $entry)
                        <li class="flex items-start gap-3 text-sm">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-600"></span>
                            <div>
                                <p class="font-medium text-slate-900">
                                    {{ $entry->from_status ? ucfirst(str_replace('_',' ',$entry->from_status)).' → ' : '' }}{{ ucfirst(str_replace('_',' ',$entry->to_status)) }}
                                </p>
                                @if ($entry->note)
                                    <p class="text-slate-500">{{ $entry->note }}</p>
                                @endif
                                <p class="text-xs text-slate-400">{{ $entry->changedBy?->name ?? 'System' }} &middot; {{ $entry->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </li>
                    @empty
                        <p class="text-sm text-slate-400">No status changes yet.</p>
                    @endforelse
                </ol>
            </x-eva.card>
        </div>

        <div class="space-y-6">
            <x-eva.card title="Order Summary">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>TZS {{ number_format((float) $order->subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">VAT ({{ $order->vatRatePercent() }}%)</span><span>TZS {{ number_format((float) $order->vat_amount) }}</span></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 font-semibold text-slate-900"><span>Total</span><span>TZS {{ number_format((float) $order->total) }}</span></div>
                </div>
                @if ($order->rejection_reason)
                    <div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        <p class="font-medium">Rejection Reason</p>
                        <p>{{ $order->rejection_reason }}</p>
                    </div>
                @endif
                @if (! in_array($order->status, ['pending_confirmation', 'rejected'], true))
                    <x-eva.button variant="secondary" :href="route('orders.pdf', $order)" class="mt-4 w-full justify-center">
                        Download Invoice PDF
                    </x-eva.button>
                @endif
            </x-eva.card>

            @if ($nextStatus)
                <x-eva.card title="Fulfillment Progress">
                    <p class="mb-3 text-sm text-slate-500">
                        Current status: <strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong>
                    </p>
                    <form method="POST" action="{{ route('orders.progress', $order) }}">
                        @csrf
                        <x-eva.button type="submit" class="w-full justify-center">
                            Mark as {{ ucfirst(str_replace('_', ' ', $nextStatus)) }}
                        </x-eva.button>
                    </form>
                </x-eva.card>
            @endif

            @if ($order->status === 'pending_confirmation')
                <x-eva.card title="Actions" x-data="{ panel: null }">
                    <div class="space-y-2">
                        <x-eva.button type="button" @click="panel = (panel === 'confirm' ? null : 'confirm')" class="w-full justify-center">
                            Confirm Full Order
                        </x-eva.button>
                        <x-eva.button type="button" variant="secondary" @click="panel = (panel === 'partial' ? null : 'partial')" class="w-full justify-center">
                            Partially Fulfill
                        </x-eva.button>
                        <x-eva.button type="button" variant="danger" @click="panel = (panel === 'reject' ? null : 'reject')" class="w-full justify-center">
                            Reject Order
                        </x-eva.button>
                    </div>

                    {{-- CONFIRM PANEL --}}
                    <div x-show="panel === 'confirm'" x-cloak class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                        <form method="POST" action="{{ route('orders.confirm', $order) }}">
                            @csrf
                            @if (! $order->warehouse_id)
                                <label class="mb-1 block text-sm font-medium text-slate-700">Fulfilling Warehouse</label>
                                <select name="warehouse_id" required class="mb-3 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Select a warehouse&hellip;</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <p class="mb-3 text-xs text-slate-500">This deducts stock for every line at full ordered quantity and moves the order to Processing.</p>
                            <x-eva.button type="submit" class="w-full justify-center">Confirm &amp; Deduct Stock</x-eva.button>
                        </form>
                    </div>

                    {{-- PARTIAL FULFILL PANEL --}}
                    <div x-show="panel === 'partial'" x-cloak class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                        <form method="POST" action="{{ route('orders.partially-fulfill', $order) }}">
                            @csrf
                            @if (! $order->warehouse_id)
                                <label class="mb-1 block text-sm font-medium text-slate-700">Fulfilling Warehouse</label>
                                <select name="warehouse_id" required class="mb-3 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Select a warehouse&hellip;</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="space-y-3">
                                @foreach ($order->items as $item)
                                    @php $available = $stockByLine[$item->id] ?? 0; @endphp
                                    <div class="rounded-lg border border-slate-200 p-3">
                                        <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                        <p class="mb-2 text-sm font-medium text-slate-900">{{ $item->product_name }}</p>
                                        <div class="flex items-end gap-2">
                                            <div>
                                                <label class="mb-1 block text-xs text-slate-500">Fulfill Qty (of {{ $item->quantity }}, {{ $available }} available)</label>
                                                <input type="number" name="items[{{ $loop->index }}][fulfilled_quantity]"
                                                       value="{{ min($item->quantity, $available) }}" min="0" max="{{ $item->quantity }}"
                                                       class="w-24 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs text-slate-500">Remainder</label>
                                                <select name="items[{{ $loop->index }}][action]" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                                    <option value="backorder">Backorder</option>
                                                    <option value="drop">Drop</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <x-eva.button type="submit" class="mt-3 w-full justify-center">Save Partial Fulfillment</x-eva.button>
                        </form>
                    </div>

                    {{-- REJECT PANEL --}}
                    <div x-show="panel === 'reject'" x-cloak class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                        <form method="POST" action="{{ route('orders.reject', $order) }}">
                            @csrf
                            <label class="mb-1 block text-sm font-medium text-slate-700">Reason (sent to customer)</label>
                            <textarea name="reason" required rows="3" class="mb-3 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            <x-eva.button type="submit" variant="danger" class="w-full justify-center">Confirm Rejection</x-eva.button>
                        </form>
                    </div>
                </x-eva.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
