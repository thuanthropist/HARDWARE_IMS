<x-layouts.admin title="Sale {{ $sale->sale_number }}" subtitle="{{ $sale->created_at->format('d M Y H:i') }}">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('pos.sales.pdf', $sale)">Download Receipt PDF</x-eva.button>
    </x-slot:headerActions>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-eva.card title="Items">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="py-2">Item</th>
                            <th class="py-2 text-right">Qty</th>
                            <th class="py-2 text-right">Unit Price</th>
                            <th class="py-2 text-right">Discount</th>
                            <th class="py-2 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="py-2 font-medium text-slate-900">{{ $item->product_name }}</td>
                                <td class="py-2 text-right">{{ $item->quantity }}</td>
                                <td class="py-2 text-right">{{ number_format((float) $item->unit_price) }}</td>
                                <td class="py-2 text-right">{{ $item->discount_amount > 0 ? number_format((float) $item->discount_amount) : '—' }}</td>
                                <td class="py-2 text-right font-medium">{{ number_format((float) $item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-eva.card>
        </div>

        <div class="space-y-6">
            <x-eva.card title="Sale Details">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Cashier</span><span>{{ $sale->cashier->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Warehouse</span><span>{{ $sale->warehouse->name }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Payment</span><span>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span></div>
                    @if ($sale->payment_reference)
                        <div class="flex justify-between"><span class="text-slate-500">Reference</span><span>{{ $sale->payment_reference }}</span></div>
                    @endif
                    @if ($sale->customer_name)
                        <div class="flex justify-between"><span class="text-slate-500">Customer</span><span>{{ $sale->customer_name }}</span></div>
                    @endif
                </div>
            </x-eva.card>

            <x-eva.card title="Total">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>TZS {{ number_format((float) $sale->subtotal) }}</span></div>
                    @if ($sale->discount_amount > 0)
                        <div class="flex justify-between"><span class="text-slate-500">Discount</span><span>-TZS {{ number_format((float) $sale->discount_amount) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-slate-500">VAT ({{ $sale->vatRatePercent() }}%)</span><span>TZS {{ number_format((float) $sale->vat_amount) }}</span></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold"><span>Total</span><span>TZS {{ number_format((float) $sale->total) }}</span></div>
                </div>
            </x-eva.card>
        </div>
    </div>
</x-layouts.admin>
