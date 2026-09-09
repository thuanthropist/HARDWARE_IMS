@php
    $selectedSupplier = old('supplier_id', $purchaseOrder->supplier_id ?? null);
    $initialItems = old('items', $purchaseOrder?->items->map(fn ($item) => [
        'product_variant_id' => $item->product_variant_id,
        'quantity_ordered' => $item->quantity_ordered,
        'unit_cost' => (float) $item->unit_cost,
    ])->all() ?? []);
@endphp

<form
    method="POST"
    action="{{ $purchaseOrder ? route('purchase-orders.update', $purchaseOrder) : route('purchase-orders.store') }}"
    class="space-y-6"
    x-data="purchaseOrderForm({
        suppliers: @js($suppliersJson),
        variants: @js($variants),
        selectedSupplier: {{ $selectedSupplier ?? 'null' }},
        initialItems: @js($initialItems),
    })"
>
    @csrf
    @if ($purchaseOrder)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label for="supplier_id" class="mb-1 block text-sm font-medium text-slate-700">Supplier <span class="text-red-500">*</span></label>
            <select id="supplier_id" name="supplier_id" x-model.number="supplierId"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                <option value="">Select supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
            @error('supplier_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <x-eva.select name="warehouse_id" label="Destination Warehouse" required placeholder="Select warehouse"
            :options="$warehouses->pluck('name', 'id')" :value="$purchaseOrder->warehouse_id ?? ''" />

        <x-eva.input name="expected_date" label="Expected Date" type="date" :value="optional($purchaseOrder?->expected_date)->format('Y-m-d')" />
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Line Items</h3>
            <label class="flex items-center gap-1.5 text-xs text-slate-500">
                <input type="checkbox" x-model="showAllProducts" class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-700">
                Show all products (not just this supplier's departments)
            </label>
        </div>

        <p x-show="supplierId && supplierDepartmentIds.length && !showAllProducts" x-cloak class="mb-3 text-xs text-emerald-700">
            Showing products from this supplier's tagged departments only.
        </p>

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-2 items-start">
                    <div class="col-span-6">
                        <select :name="'items[' + index + '][product_variant_id]'" x-model.number="item.product_variant_id" @change="onProductChange(item)"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select product</option>
                            <template x-for="variant in filteredVariants" :key="variant.id">
                                <option :value="variant.id" x-text="variant.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <input type="number" min="1" :name="'items[' + index + '][quantity_ordered]'" x-model.number="item.quantity_ordered"
                               placeholder="Qty"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="col-span-3">
                        <input type="number" min="0" step="0.01" :name="'items[' + index + '][unit_cost]'" x-model.number="item.unit_cost"
                               placeholder="Unit cost (TZS)"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="col-span-1 pt-2">
                        <button type="button" @click="removeItem(index)" class="text-sm text-red-500 hover:text-red-700">✕</button>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="addItem" class="mt-3 text-sm font-medium text-emerald-700 hover:text-emerald-900">+ Add line item</button>

        @error('items')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

        <div class="mt-4 flex justify-end border-t border-slate-100 pt-3 text-sm">
            <span class="text-slate-500">Estimated Total:&nbsp;</span>
            <span class="font-semibold text-slate-900" x-text="new Intl.NumberFormat('en-TZ').format(total) + ' TZS'"></span>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <x-eva.button variant="secondary" :href="route('purchase-orders.index')">Cancel</x-eva.button>
        <x-eva.button type="submit">{{ $purchaseOrder ? 'Save Changes' : 'Create Draft PO' }}</x-eva.button>
    </div>
</form>
