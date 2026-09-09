<x-layouts.admin title="New Stock Transfer">
    <x-eva.card>
        <form method="POST" action="{{ route('stock-transfers.store') }}" class="space-y-6"
              x-data="stockTransferForm({ variants: @js($variants) })">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-eva.select name="from_warehouse_id" label="From Warehouse" required placeholder="Select source warehouse"
                    :options="$warehouses->pluck('name', 'id')" />
                <x-eva.select name="to_warehouse_id" label="To Warehouse" required placeholder="Select destination warehouse"
                    :options="$warehouses->pluck('name', 'id')" />
            </div>
            @error('to_warehouse_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

            <div class="rounded-lg border border-slate-200 p-4">
                <h3 class="mb-3 text-sm font-semibold text-slate-900">Items to Transfer</h3>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-start">
                            <div class="col-span-9">
                                <select :name="'items[' + index + '][product_variant_id]'" x-model.number="item.product_variant_id"
                                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
                                    <option value="">Select product</option>
                                    <template x-for="variant in variants" :key="variant.id">
                                        <option :value="variant.id" x-text="variant.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="number" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity"
                                       placeholder="Qty"
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
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-eva.button variant="secondary" :href="route('stock-transfers.index')">Cancel</x-eva.button>
                <x-eva.button type="submit">Create Transfer</x-eva.button>
            </div>
        </form>
    </x-eva.card>
</x-layouts.admin>
