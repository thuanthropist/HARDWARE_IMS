<x-layouts.storefront title="Your Cart">
    <div class="container" style="padding-top: 7.5rem;">
        <h1 class="fw-bold mb-4" data-aos="fade-up">Your Cart</h1>

        @if ($cart->items->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center" data-aos="fade-up">
                <p class="fs-5 mb-3">Your cart is empty.</p>
                <a href="{{ route('storefront.home') }}" class="btn btn-primary">Start Shopping</a>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    @foreach ($groups as $label => $items)
                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up">
                            <h6 class="fw-bold mb-3">{{ $label }}</h6>

                            @foreach ($items as $item)
                                @php
                                    $availableStock = $item->productVariant?->availableStock ?? $item->product->currentStock;
                                    $exceedsStock = $item->quantity > $availableStock;
                                @endphp
                                <div class="d-flex flex-wrap align-items-center gap-3 py-3 border-bottom">
                                    <div class="sf-product-thumb rounded-3" style="width: 64px; height: 64px; flex-shrink: 0;">
                                        <span class="fs-4">{{ $item->product->department->emoji }}</span>
                                    </div>

                                    <div class="flex-grow-1" style="min-width: 180px;">
                                        <a href="{{ route('storefront.products.show', $item->product->slug) }}" class="fw-semibold text-reset text-decoration-none">
                                            {{ $item->product->name }}
                                        </a>
                                        @if ($item->productVariant && $item->productVariant->variant_name !== 'Standard')
                                            <div class="small text-body-secondary">{{ $item->productVariant->variant_name }}</div>
                                        @endif
                                        <div class="small text-body-secondary">TZS {{ number_format((float) $item->unit_price, 0) }} each</div>

                                        @if ($exceedsStock)
                                            <div class="small text-danger fw-semibold mt-1">
                                                Only {{ max($availableStock, 0) }} left in stock — please reduce quantity.
                                            </div>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('storefront.cart.items.update', $item) }}" class="d-flex align-items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control form-control-sm" style="width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                                    </form>

                                    <div class="fw-bold text-nowrap" style="min-width: 100px; text-align: right;">
                                        TZS {{ number_format($item->lineTotal, 0) }}
                                    </div>

                                    <form method="POST" action="{{ route('storefront.cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger">Remove</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-up" style="position: sticky; top: 6.5rem;">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-body-secondary">Subtotal</span>
                            <span>TZS {{ number_format($cart->subtotal, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-body-secondary">VAT ({{ (int) round(setting('tax.vat_rate', 0.18) * 100) }}%)</span>
                            <span>TZS {{ number_format($cart->vatAmount, 0) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                            <span>Total</span>
                            <span style="color: var(--sf-primary);">TZS {{ number_format($cart->total, 0) }}</span>
                        </div>

                        @if ($cart->items->contains(fn ($item) => $item->quantity > ($item->productVariant?->availableStock ?? $item->product->currentStock)))
                            <div class="alert alert-warning small mb-3">
                                Some items exceed available stock. Please adjust quantities before checkout.
                            </div>
                        @endif

                        <a href="{{ route('storefront.checkout.show') }}" class="btn btn-primary w-100 mb-2">Proceed to Checkout</a>
                        <a href="{{ route('storefront.quotes.create') }}" class="btn btn-outline-primary w-100">Request a Quote Instead</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.storefront>
