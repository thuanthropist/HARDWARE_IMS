<x-layouts.admin title="Point of Sale" subtitle="Fast counter checkout for walk-in customers">
    <x-slot:headerActions>
        <x-eva.button variant="secondary" :href="route('pos.sales.index')">Sales History</x-eva.button>
    </x-slot:headerActions>

    <div x-data="posTerminal('{{ route('pos.lookup') }}', @js($canDiscount), @js((float) setting('tax.vat_rate', 0.18)))" @barcode-scanned.window="handleScan($event.detail.code)">
        <form method="POST" action="{{ route('pos.store') }}" @submit="if (items.length === 0) { $event.preventDefault(); }">
            @csrf

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <x-eva.card title="Scan or Search">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1" style="min-width: 220px;">
                                <label class="mb-1 block text-sm font-medium text-slate-700">SKU or Barcode</label>
                                <input type="text" x-model="code" @keydown.enter.prevent="lookup()" autofocus
                                       placeholder="Scan or type a SKU/barcode, then press Enter"
                                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <x-eva.button type="button" @click="lookup()">Add Item</x-eva.button>
                            <x-eva.barcode-scanner label="Scan Camera" />
                        </div>
                        <p x-show="error" x-text="error" x-cloak class="mt-2 text-sm text-red-600"></p>
                    </x-eva.card>

                    <x-eva.card title="Cart">
                        <template x-if="items.length === 0">
                            <p class="py-6 text-center text-sm text-slate-400">No items yet — scan or search a product to begin.</p>
                        </template>

                        <div class="space-y-2">
                            <template x-for="(item, index) in items" :key="item.product_variant_id">
                                <div class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 p-3">
                                    <input type="hidden" :name="'items[' + index + '][product_variant_id]'" :value="item.product_variant_id">
                                    <div class="min-w-[160px] flex-1">
                                        <p class="text-sm font-medium text-slate-900" x-text="item.name"></p>
                                        <p class="text-xs text-slate-400" x-text="item.sku + ' &middot; ' + item.available + ' available'"></p>
                                    </div>
                                    <div>
                                        <label class="mb-0.5 block text-xs text-slate-500">Qty</label>
                                        <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1"
                                               class="w-20 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-0.5 block text-xs text-slate-500">Unit Price</label>
                                        <p class="px-2 py-1.5 text-sm" x-text="money(item.unit_price)"></p>
                                    </div>
                                    <template x-if="canDiscount">
                                        <div>
                                            <label class="mb-0.5 block text-xs text-slate-500">Discount (TZS)</label>
                                            <input type="number" :name="'items[' + index + '][discount_amount]'" x-model.number="item.discount_amount" min="0"
                                                   class="w-24 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                        </div>
                                    </template>
                                    <div class="w-28 text-right text-sm font-semibold" x-text="'TZS ' + money(lineTotal(item))"></div>
                                    <button type="button" @click="remove(index)" class="text-sm text-red-500 hover:text-red-700">Remove</button>
                                </div>
                            </template>
                        </div>
                    </x-eva.card>
                </div>

                <div class="space-y-6">
                    <x-eva.card title="Warehouse">
                        <select name="warehouse_id" required class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </x-eva.card>

                    <x-eva.card title="Customer (optional)">
                        <div class="space-y-3">
                            <input type="text" name="customer_name" placeholder="Customer name" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <input type="text" name="customer_phone" placeholder="Phone number" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </x-eva.card>

                    <x-eva.card title="Payment">
                        <select name="payment_method" required class="mb-3 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Card</option>
                        </select>
                        <input type="text" name="payment_reference" placeholder="Reference / transaction code (optional)" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </x-eva.card>

                    <x-eva.card title="Total">
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span x-text="'TZS ' + money(grossSubtotal)"></span></div>
                            <div class="flex justify-between" x-show="discountTotal > 0"><span class="text-slate-500">Discount</span><span x-text="'-TZS ' + money(discountTotal)"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">VAT ({{ (int) round(setting('tax.vat_rate', 0.18) * 100) }}%)</span><span x-text="'TZS ' + money(vat)"></span></div>
                            <div class="flex justify-between border-t border-slate-100 pt-2 text-base font-bold"><span>Total</span><span x-text="'TZS ' + money(total)"></span></div>
                        </div>
                        <x-eva.button type="submit" class="mt-4 w-full justify-center">
                            Complete Sale
                        </x-eva.button>
                    </x-eva.card>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
