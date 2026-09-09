<x-layouts.admin title="New Stock Adjustment">
    <x-eva.card>
        <p class="mb-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700">
            Adjustments of more than {{ number_format($thresholdQuantity) }} units or worth more than {{ number_format($thresholdValue) }} TZS
            require approval from a Manager or Admin before stock is changed.
        </p>

        <form method="POST" action="{{ route('stock-adjustments.store') }}" class="space-y-4"
              x-data="{
                  variantsList: @js($variants),
                  notFound: false,
                  handleScan(code) {
                      this.notFound = false;
                      const match = this.variantsList.find((v) => v.sku === code || v.barcode === code);
                      if (match) {
                          this.$refs.productSelect.value = match.id;
                      } else {
                          this.notFound = true;
                      }
                  },
              }"
              @barcode-scanned.window="handleScan($event.detail.code)"
        >
            @csrf

            <div>
                <div class="mb-1 flex items-center justify-between">
                    <label for="product_variant_id" class="block text-sm font-medium text-slate-700">Product <span class="text-red-500">*</span></label>
                    <x-eva.barcode-scanner label="Scan" />
                </div>
                <select name="product_variant_id" id="product_variant_id" x-ref="productSelect" required
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                    <option value="">Select product</option>
                    @foreach ($variants as $variant)
                        <option value="{{ $variant['id'] }}">{{ $variant['label'] }}</option>
                    @endforeach
                </select>
                <p x-show="notFound" x-cloak class="mt-1 text-xs text-red-600">No product matches that code.</p>
                @error('product_variant_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <x-eva.select name="warehouse_id" label="Warehouse" required placeholder="Select warehouse"
                :options="$warehouses->pluck('name', 'id')" />

            <div class="grid grid-cols-2 gap-4">
                <x-eva.select name="type" label="Adjustment Type" required
                    :options="['add' => 'Add to stock', 'remove' => 'Remove from stock']" />

                <x-eva.input name="quantity" label="Quantity" type="number" min="1" required />
            </div>

            <x-eva.select name="reason" label="Reason" required placeholder="Select reason"
                :options="[
                    'damage' => 'Damage',
                    'loss' => 'Loss',
                    'count_correction' => 'Count Correction',
                    'expiry' => 'Expiry',
                    'breakage' => 'Breakage',
                ]" />

            <x-eva.textarea name="note" label="Note" required placeholder="Describe what happened — mandatory for the audit trail" />

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('stock-adjustments.index')">Cancel</x-eva.button>
                <x-eva.button type="submit">Submit Adjustment</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>
