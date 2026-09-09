<x-layouts.admin :title="'Receive ' . $purchaseOrder->po_number" :subtitle="'From ' . $purchaseOrder->supplier->name . ' into ' . $purchaseOrder->warehouse->name">
    <x-eva.card>
        <p class="mb-4 text-sm text-slate-500">
            Confirm quantities as you physically check them against this list. Attributes are shown so warehouse staff can visually
            verify the item matches the line, not just the SKU code.
        </p>

        <form method="POST" action="{{ route('purchase-orders.receive.store', $purchaseOrder) }}" class="space-y-4">
            @csrf

            @error('items')<p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ $message }}</p>@enderror

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Ordered</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Already Received</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Outstanding</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Receive Now</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($purchaseOrder->items as $index => $item)
                            @php
                                $attributeSummary = $item->productVariant->product->productAttributes
                                    ->map(fn ($attr) => $attr->attribute_value . ($attr->attribute_unit ? $attr->attribute_unit : ''))
                                    ->implode(', ');
                                $remaining = $item->quantity_remaining;
                                $tracksBatches = $item->productVariant->product->track_batches;
                            @endphp
                            <tr class="{{ $remaining <= 0 ? 'opacity-50' : '' }}">
                                <td class="px-3 py-2">
                                    <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                    <p class="font-medium text-slate-900">{{ $item->productVariant->product->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $item->productVariant->sku }}</p>
                                    @if ($attributeSummary)
                                        <p class="mt-0.5 text-xs font-medium text-emerald-700">{{ $attributeSummary }}</p>
                                    @endif
                                    @if ($tracksBatches)
                                        <p class="mt-0.5 text-xs font-medium text-amber-600">Batch-tracked</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">{{ number_format($item->quantity_ordered) }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($item->quantity_received) }}</td>
                                <td class="px-3 py-2 text-right font-medium {{ $remaining > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ number_format($remaining) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" min="0" max="{{ $remaining }}"
                                           name="items[{{ $index }}][quantity_received_now]"
                                           value="{{ old('items.'.$index.'.quantity_received_now', $remaining > 0 ? $remaining : 0) }}"
                                           @if ($remaining <= 0) disabled @endif
                                           class="w-24 rounded-lg border border-slate-300 px-2 py-1.5 text-right text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                                    @error('items.'.$index.'.quantity_received_now')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                            @if ($tracksBatches && $remaining > 0)
                                <tr class="bg-amber-50/50">
                                    <td colspan="5" class="px-3 py-2">
                                        <div class="flex flex-wrap items-end gap-3">
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-600">Batch Number <span class="text-red-500">*</span></label>
                                                <input type="text" name="items[{{ $index }}][batch_number]"
                                                       value="{{ old('items.'.$index.'.batch_number') }}"
                                                       class="w-40 rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                                                @error('items.'.$index.'.batch_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-slate-600">Expiry Date <span class="text-red-500">*</span></label>
                                                <input type="date" name="items[{{ $index }}][expiry_date]"
                                                       value="{{ old('items.'.$index.'.expiry_date') }}"
                                                       class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                                                @error('items.'.$index.'.expiry_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('purchase-orders.show', $purchaseOrder)">Cancel</x-eva.button>
                <x-eva.button type="submit">Confirm Receipt</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>
