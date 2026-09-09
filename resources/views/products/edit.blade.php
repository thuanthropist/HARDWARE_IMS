<x-layouts.admin title="Edit Product" :subtitle="$product->name">
    <div class="space-y-6">
        <x-eva.card>
            @include('products._form')
        </x-eva.card>

        <x-eva.card title="Barcodes & Labels">
            <p class="mb-4 text-sm text-slate-500">One Code128 barcode per variant SKU. Print a label to affix to shelves or packaging.</p>

            <div class="space-y-3">
                @foreach ($product->variants as $variant)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center gap-4">
                            <img src="{{ route('product-variants.barcode', $variant) }}" alt="{{ $variant->sku }}" class="h-12 bg-white">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $variant->variant_name }}</p>
                                <p class="font-mono text-xs text-slate-500">{{ $variant->sku }}</p>
                            </div>
                        </div>
                        <a href="{{ route('product-variants.label', $variant) }}" target="_blank"
                           class="text-sm font-medium text-emerald-700 hover:text-emerald-900">Print Label →</a>
                    </div>
                @endforeach
            </div>
        </x-eva.card>
    </div>
</x-layouts.admin>
