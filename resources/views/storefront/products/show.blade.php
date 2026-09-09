<x-layouts.storefront :title="$product->name">
    <div class="container" style="padding-top: 7.5rem;">
        <nav class="small text-body-secondary mb-4" data-aos="fade-up">
            <a href="{{ route('storefront.home') }}">Home</a> /
            <a href="{{ route('storefront.departments.show', $product->department->slug) }}">{{ $product->department->name }}</a> /
            <span>{{ $product->name }}</span>
        </nav>

        <div class="row g-5">
            {{-- IMAGE / THUMB --}}
            <div class="col-lg-5" data-aos="fade-up">
                <div class="sf-product-thumb rounded-4" style="aspect-ratio: 1 / 1;">
                    @if ($product->image_path)
                        <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}">
                    @else
                        <span style="font-size: 5rem;">{{ $product->department->emoji }}</span>
                    @endif
                </div>
            </div>

            {{-- DETAILS --}}
            <div class="col-lg-7" data-aos="fade-up" data-aos-delay="80">
                <div class="mb-2">
                    <x-storefront.stock-badge :status="$product->stockStatus" />
                    @if ($product->brand)
                        <span class="small text-body-secondary ms-2">{{ $product->brand->name }}</span>
                    @endif
                </div>

                <h1 class="fw-bold mb-2" style="font-size: 1.9rem;">{{ $product->name }}</h1>
                <p class="text-body-secondary small mb-3">SKU: {{ $product->sku }} &middot; {{ $product->category->name ?? '' }}</p>

                <p class="mb-4">{{ $product->description }}</p>

                <form method="POST" action="{{ route('storefront.cart.items.store') }}" class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if ($product->variants->count() > 1)
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Option</label>
                            <select name="product_variant_id" class="form-select">
                                @foreach ($product->variants as $variant)
                                    <option value="{{ $variant->id }}">
                                        {{ $variant->variant_name }}
                                        &mdash; TZS {{ number_format((float) $product->selling_price + (float) $variant->additional_price, 0) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="product_variant_id" value="{{ $product->variants->first()?->id }}">
                        <div class="fs-3 fw-bold mb-3" style="color: var(--sf-primary);">
                            TZS {{ number_format((float) $product->selling_price + (float) ($product->variants->first()?->additional_price ?? 0), 0) }}
                            <span class="fs-6 text-body-secondary fw-normal">/ {{ $product->unit_of_measure }}</span>
                        </div>
                    @endif

                    <div class="d-flex align-items-center gap-3">
                        <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width: 100px;">
                        <button type="submit" class="btn btn-primary flex-grow-1" @disabled($product->stockStatus === 'out_of_stock')>
                            {{ $product->stockStatus === 'out_of_stock' ? 'Out of Stock' : 'Add to Cart' }}
                        </button>
                    </div>
                </form>

                @if ($calculatorTypes->isNotEmpty())
                    <div class="card border-0 rounded-4 p-4 mb-4" style="background: color-mix(in srgb, var(--sf-primary) 8%, white);">
                        <p class="fw-semibold mb-2">Not sure how much you need?</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($calculatorTypes as $tool)
                                <a href="{{ route('storefront.tools.show', $tool->key) }}" class="btn btn-outline-primary btn-sm">
                                    Try the {{ $tool->name }} &rarr;
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-3 mb-2">
                    <a href="{{ route('storefront.quotes.create') }}?product={{ $product->slug }}" class="small">Request a professional quote for this item &rarr;</a>
                    <x-storefront.whatsapp-link
                        class="small text-success fw-semibold"
                        :message="'Hi! I have a question about '.$product->name.' (SKU: '.$product->sku.').'"
                    >
                        Ask about this item on WhatsApp &rarr;
                    </x-storefront.whatsapp-link>
                </div>

                @if ($specs->isNotEmpty())
                    <h6 class="fw-bold mt-4 mb-3">Specifications</h6>
                    <table class="table table-sm">
                        <tbody>
                            @foreach ($specs as $spec)
                                <tr>
                                    <td class="text-body-secondary" style="width: 40%;">{{ $spec['label'] }}</td>
                                    <td class="fw-semibold">{{ $spec['value'] }}{{ $spec['unit'] ? ' '.$spec['unit'] : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="sf-section">
                <h4 class="fw-bold mb-4" data-aos="fade-up">More from {{ $product->department->name }}</h4>
                <div class="row g-4">
                    @foreach ($relatedProducts as $index => $related)
                        <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                            <x-storefront.product-card :product="$related" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.storefront>
