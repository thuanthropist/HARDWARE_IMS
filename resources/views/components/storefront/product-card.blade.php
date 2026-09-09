@props(['product'])

<div class="sf-product-card">
    <a href="{{ route('storefront.products.show', $product->slug) }}" class="text-decoration-none text-reset d-block">
        <div class="sf-product-thumb">
            @if ($product->image_path)
                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <span style="font-size: 2.5rem;">{{ $product->department->emoji }}</span>
            @endif
        </div>
        <div class="p-3">
            <div class="mb-2">
                <x-storefront.stock-badge :status="$product->stockStatus" />
            </div>
            <h6 class="fw-bold mb-1 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
            <p class="small text-body-secondary mb-2 text-truncate">{{ $product->category->name ?? '' }}</p>
            <div class="fw-bold" style="color: var(--sf-primary);">
                TZS {{ number_format((float) $product->selling_price, 0) }}
            </div>
        </div>
    </a>
</div>
