@php
    $statusColor = match($quote->status) {
        'requested' => 'amber',
        'reviewed', 'sent' => 'sky',
        'accepted' => 'emerald',
        'rejected', 'expired' => 'red',
        default => 'slate',
    };
    $editable = in_array($quote->status, ['requested', 'reviewed'], true);
    $initialItems = $quote->items->map(fn ($item) => [
        'product_id' => $item->product_id,
        'name' => $item->product->name ?? null,
        'description' => $item->description,
        'quantity' => $item->quantity,
        'unit_price' => (float) $item->unit_price,
    ])->values();
@endphp

<script>
    function quoteEditor(initialItems, searchUrl, vatRate) {
        return {
            items: initialItems,
            vatRate: vatRate,
            query: '',
            results: [],
            addProduct(product) {
                this.items.push({ product_id: product.id, name: product.name, description: null, quantity: 1, unit_price: parseFloat(product.selling_price) });
                this.query = '';
                this.results = [];
            },
            addLabor() {
                this.items.push({ product_id: null, name: null, description: '', quantity: 1, unit_price: 0 });
            },
            remove(index) {
                this.items.splice(index, 1);
            },
            lineTotal(item) {
                return (parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 0);
            },
            money(value) {
                return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
            },
            get subtotal() { return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0); },
            get vat() { return this.subtotal * this.vatRate; },
            get total() { return this.subtotal + this.vat; },
            async search() {
                if (this.query.length < 2) { this.results = []; return; }
                const res = await fetch(searchUrl + '?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
            },
        };
    }
</script>

<x-layouts.admin title="Quote {{ $quote->quote_number }}" subtitle="Requested {{ $quote->created_at->format('d M Y H:i') }}">
    <x-slot:headerActions>
        <x-eva.badge :color="$statusColor">{{ ucfirst($quote->status) }}</x-eva.badge>
    </x-slot:headerActions>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-eva.card title="Customer">
                <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-slate-500">Name</p>
                        <p class="font-medium text-slate-900">{{ $quote->name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Email</p>
                        <p class="font-medium text-slate-900">{{ $quote->email }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Phone</p>
                        <p class="font-medium text-slate-900">{{ $quote->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Preferred Contact</p>
                        <p class="font-medium text-slate-900">{{ $quote->preferred_contact_method ? ucfirst($quote->preferred_contact_method) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Timeline</p>
                        <p class="font-medium text-slate-900">{{ $quote->timeline ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Source</p>
                        <p class="font-medium text-slate-900">{{ ucfirst($quote->source) }}</p>
                    </div>
                </div>
                @if ($quote->project_description)
                    <div class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                        <p class="mb-1 font-medium text-slate-500">Project Description</p>
                        {{ $quote->project_description }}
                    </div>
                @endif
                @if ($quote->calculatorSubmission)
                    <div class="mt-3 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">
                        Based on a <strong>{{ $quote->calculatorSubmission->calculatorType->name }}</strong> submission
                        (est. {{ number_format((float) $quote->calculatorSubmission->estimated_total) }} TZS).
                    </div>
                @endif
            </x-eva.card>

            @if ($editable)
                <x-eva.card title="Line Items">
                <div x-data="quoteEditor(@js($initialItems), '{{ route('quotes.search-products') }}', @js((float) setting('tax.vat_rate', 0.18)))">
                    <form method="POST" action="{{ route('quotes.update', $quote) }}">
                        @csrf
                        @method('PATCH')

                        <div class="relative mb-4">
                            <input type="text" x-model="query" @input.debounce.300ms="search()"
                                   placeholder="Search products by name or SKU to add…"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                            <div x-show="results.length > 0" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                                <template x-for="product in results" :key="product.id">
                                    <button type="button" @click="addProduct(product)" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm last:border-0 hover:bg-slate-50">
                                        <span x-text="product.name"></span>
                                        <span class="text-xs text-slate-400" x-text="'(' + product.sku + ') — TZS ' + Number(product.selling_price).toLocaleString()"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 p-3">
                                    <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">

                                    <div class="min-w-[160px] flex-1">
                                        <template x-if="item.product_id">
                                            <p class="text-sm font-medium text-slate-900" x-text="item.name"></p>
                                        </template>
                                        <template x-if="!item.product_id">
                                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description"
                                                   placeholder="e.g. Installation labor (2 days)"
                                                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                        </template>
                                    </div>

                                    <div>
                                        <label class="mb-0.5 block text-xs text-slate-500">Qty</label>
                                        <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1"
                                               class="w-20 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-0.5 block text-xs text-slate-500">Unit Price (TZS)</label>
                                        <input type="number" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" min="0" step="0.01"
                                               class="w-32 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="w-28 text-right text-sm font-medium" x-text="'TZS ' + money(lineTotal(item))"></div>
                                    <button type="button" @click="remove(index)" class="text-sm text-red-500 hover:text-red-700">Remove</button>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addLabor()" class="mt-3 text-sm font-medium text-emerald-700 hover:text-emerald-900">
                            + Add Labor / Custom Line
                        </button>

                        <div class="mt-4 ml-auto w-64 space-y-1 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span x-text="'TZS ' + money(subtotal)"></span></div>
                            <div class="flex justify-between"><span class="text-slate-500">VAT ({{ (int) round(setting('tax.vat_rate', 0.18) * 100) }}%)</span><span x-text="'TZS ' + money(vat)"></span></div>
                            <div class="flex justify-between border-t border-slate-100 pt-1 font-semibold"><span>Total</span><span x-text="'TZS ' + money(total)"></span></div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Valid Until</label>
                                <input type="date" name="valid_until" value="{{ old('valid_until', $quote->valid_until?->toDateString() ?? now()->addDays(14)->toDateString()) }}"
                                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Internal Notes</label>
                            <textarea name="internal_notes" rows="2" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('internal_notes', $quote->internal_notes) }}</textarea>
                        </div>

                        <x-eva.button type="submit" class="mt-4">Save &amp; Mark Reviewed</x-eva.button>
                    </form>
                </div>
                </x-eva.card>
            @else
                <x-eva.card title="Line Items">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="py-2">Item</th>
                                <th class="py-2 text-right">Qty</th>
                                <th class="py-2 text-right">Unit Price</th>
                                <th class="py-2 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($quote->items as $item)
                                <tr>
                                    <td class="py-2 font-medium text-slate-900">{{ $item->label }}</td>
                                    <td class="py-2 text-right">{{ $item->quantity }}</td>
                                    <td class="py-2 text-right">{{ number_format((float) $item->unit_price) }}</td>
                                    <td class="py-2 text-right font-medium">{{ number_format((float) $item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4 ml-auto w-64 space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>TZS {{ number_format((float) $quote->subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">VAT ({{ $quote->vatRatePercent() }}%)</span><span>TZS {{ number_format((float) $quote->vat_amount) }}</span></div>
                        <div class="flex justify-between border-t border-slate-100 pt-1 font-semibold"><span>Total</span><span>TZS {{ number_format((float) $quote->total) }}</span></div>
                    </div>
                </x-eva.card>
            @endif
        </div>

        <div class="space-y-6">
            <x-eva.card title="Quote Details">
                <div class="space-y-2 text-sm">
                    @if ($quote->valid_until)
                        <div class="flex justify-between"><span class="text-slate-500">Valid Until</span><span>{{ $quote->valid_until->format('d M Y') }}</span></div>
                    @endif
                    @if ($quote->sent_at)
                        <div class="flex justify-between"><span class="text-slate-500">Sent</span><span>{{ $quote->sent_at->format('d M Y H:i') }}</span></div>
                    @endif
                    @if ($quote->reviewedBy)
                        <div class="flex justify-between"><span class="text-slate-500">Reviewed By</span><span>{{ $quote->reviewedBy->name }}</span></div>
                    @endif
                </div>

                @if ($quote->items->isNotEmpty())
                    <x-eva.button variant="secondary" :href="route('quotes.pdf', $quote)" class="mt-4 w-full justify-center">Download PDF</x-eva.button>
                @endif
            </x-eva.card>

            @if (in_array($quote->status, ['requested', 'reviewed'], true))
                <x-eva.card title="Actions">
                    <form method="POST" action="{{ route('quotes.send', $quote) }}">
                        @csrf
                        <x-eva.button type="submit" class="w-full justify-center">
                            Send Quote to Customer
                        </x-eva.button>
                    </form>
                    @if ($quote->items->isEmpty())
                        <p class="mt-2 text-xs text-slate-500">Add at least one line item and save before sending.</p>
                    @endif
                </x-eva.card>
            @endif

            @if ($quote->status === 'sent')
                <x-eva.card title="Customer Response">
                    <p class="mb-3 text-sm text-slate-500">Record the customer's decision once they respond by phone, email or WhatsApp.</p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('quotes.accept', $quote) }}" class="flex-1">
                            @csrf
                            <x-eva.button type="submit" class="w-full justify-center">Mark Accepted</x-eva.button>
                        </form>
                        <form method="POST" action="{{ route('quotes.reject', $quote) }}" class="flex-1">
                            @csrf
                            <x-eva.button type="submit" variant="danger" class="w-full justify-center">Mark Rejected</x-eva.button>
                        </form>
                    </div>
                </x-eva.card>
            @endif

            @if ($quote->status === 'accepted' && ! $quote->order)
                <x-eva.card title="Convert to Order" x-data="{ deliveryMethod: 'pickup' }">
                    <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                        @csrf
                        <div class="mb-3 flex gap-4 text-sm">
                            <label class="flex items-center gap-1.5">
                                <input type="radio" name="delivery_method" value="pickup" x-model="deliveryMethod" checked> Pickup
                            </label>
                            <label class="flex items-center gap-1.5">
                                <input type="radio" name="delivery_method" value="delivery" x-model="deliveryMethod"> Delivery
                            </label>
                        </div>

                        <label class="mb-1 block text-sm font-medium text-slate-700">Fulfilling Warehouse</label>
                        <select name="warehouse_id" required class="mb-3 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Select a warehouse&hellip;</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>

                        <div x-show="deliveryMethod === 'delivery'" x-cloak class="mb-3">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Delivery Address</label>
                            <textarea name="delivery_address" rows="2" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        </div>

                        <x-eva.button type="submit" class="w-full justify-center">Create Order from Quote</x-eva.button>
                    </form>
                </x-eva.card>
            @endif

            @if ($quote->order)
                <x-eva.card title="Converted Order">
                    <p class="mb-2 text-sm text-slate-600">This quote was converted to an order.</p>
                    <x-eva.button variant="secondary" :href="route('orders.show', $quote->order)" class="w-full justify-center">
                        View Order {{ $quote->order->order_number }}
                    </x-eva.button>
                </x-eva.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
