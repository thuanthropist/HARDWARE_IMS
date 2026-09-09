<x-layouts.admin title="Product Lookup" subtitle="Scan or enter a SKU/barcode to quickly look up a product">
    <div
        x-data="{
            loading: false,
            result: null,
            notFound: false,
            async lookup(code) {
                if (!code) return;
                this.loading = true;
                this.notFound = false;
                this.result = null;
                const response = await fetch('{{ route('product-lookup.search') }}?code=' + encodeURIComponent(code));
                const data = await response.json();
                this.loading = false;
                if (data.found) {
                    this.result = data;
                } else {
                    this.notFound = true;
                }
            },
            init() {
                const prefill = {{ Js::from(request('code', '')) }};
                if (prefill) {
                    this.$refs.manualCode.value = prefill;
                    this.lookup(prefill);
                }
            },
        }"
        @barcode-scanned.window="lookup($event.detail.code)"
        class="max-w-xl"
    >
        <x-eva.card>
            <div class="flex items-center gap-3">
                <input type="text" x-ref="manualCode" @keydown.enter.prevent="lookup($refs.manualCode.value)"
                       placeholder="Type or scan a SKU / barcode"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                <x-eva.barcode-scanner label="Scan Camera" />
            </div>

            <p x-show="loading" x-cloak class="mt-4 text-sm text-slate-400">Looking up…</p>
            <p x-show="notFound" x-cloak class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">No product matches that code.</p>

            <template x-if="result">
                <div class="mt-5 border-t border-slate-100 pt-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-lg font-semibold text-slate-900" x-text="result.product.name"></p>
                            <p class="font-mono text-xs text-slate-400" x-text="result.variant.sku"></p>
                        </div>
                        <a :href="result.variant.edit_url" class="text-sm font-medium text-emerald-700 hover:text-emerald-900">Edit Product →</a>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-slate-500">Department:</span> <span x-text="result.product.department"></span></div>
                        <div><span class="text-slate-500">Category:</span> <span x-text="result.product.category"></span></div>
                        <div><span class="text-slate-500">Brand:</span> <span x-text="result.product.brand || '—'"></span></div>
                        <div><span class="text-slate-500">Price:</span> <span x-text="new Intl.NumberFormat('en-TZ').format(result.product.selling_price) + ' TZS'"></span></div>
                    </div>

                    <template x-if="Object.keys(result.product.attributes).length">
                        <div class="mt-3">
                            <p class="mb-1 text-xs font-medium uppercase text-slate-500">Attributes</p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="(value, key) in result.product.attributes" :key="key">
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800" x-text="key + ': ' + value"></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="mt-3">
                        <p class="mb-1 text-xs font-medium uppercase text-slate-500">Stock by Warehouse</p>
                        <template x-for="level in result.stock" :key="level.warehouse">
                            <div class="flex justify-between border-b border-slate-50 py-1 text-sm">
                                <span x-text="level.warehouse"></span>
                                <span class="font-medium" x-text="level.quantity"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </x-eva.card>
    </div>
</x-layouts.admin>
