<x-layouts.storefront :title="'Quote '.$quote->quote_number">
    <div class="container" style="padding-top: 7.5rem; max-width: 760px;">
        <a href="{{ route('storefront.account.quotes') }}" class="small mb-3 d-inline-block">&larr; Back to My Quotes</a>

        <div class="d-flex justify-content-between align-items-start mb-4" data-aos="fade-up">
            <div>
                <h1 class="fw-bold mb-1">{{ $quote->quote_number }}</h1>
                <p class="text-body-secondary mb-0">Requested {{ $quote->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="sf-badge-stock sf-badge-low-stock">{{ Str::headline($quote->status) }}</span>
        </div>

        @if ($quote->calculatorSubmission)
            <div class="alert alert-light border small mb-4" data-aos="fade-up">
                Based on your <strong>{{ $quote->calculatorSubmission->calculatorType->name }}</strong> results
                (est. {{ number_format((float) $quote->calculatorSubmission->estimated_total) }} TZS).
            </div>
        @endif

        @if ($quote->items->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="80">
                <h6 class="fw-bold mb-3">Items Requested</h6>
                @foreach ($quote->items as $item)
                    <div class="d-flex justify-content-between small mb-2">
                        <span>{{ $item->label }}</span>
                        <span class="text-body-secondary">
                            Qty {{ $item->quantity }}
                            @if ($item->unit_price !== null) &middot; TZS {{ number_format((float) $item->line_total, 0) }} @endif
                        </span>
                    </div>
                @endforeach
                @if ($quote->total !== null)
                    <hr>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">Subtotal</span>
                        <span>TZS {{ number_format((float) $quote->subtotal, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-body-secondary">VAT ({{ $quote->vatRatePercent() }}%)</span>
                        <span>TZS {{ number_format((float) $quote->vat_amount, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span>TZS {{ number_format((float) $quote->total, 0) }}</span>
                    </div>
                    @if ($quote->valid_until)
                        <p class="small text-body-secondary mt-2 mb-0">Valid until {{ $quote->valid_until->format('d M Y') }}</p>
                    @endif
                @endif
            </div>
        @endif

        @if ($quote->project_description)
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="120">
                <h6 class="fw-bold mb-2">Project Description</h6>
                <p class="small mb-0">{{ $quote->project_description }}</p>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="160">
            <h6 class="fw-bold mb-2">Contact Details</h6>
            <p class="small mb-1">{{ $quote->name }} &middot; {{ $quote->email }}</p>
            @if ($quote->phone)<p class="small mb-0">{{ $quote->phone }}</p>@endif
        </div>

        @if ($quote->items->isNotEmpty())
            <a href="{{ route('storefront.account.quotes.pdf', $quote->quote_number) }}" class="btn btn-outline-primary w-100" data-aos="fade-up">
                Download Quote PDF
            </a>
        @endif
    </div>
</x-layouts.storefront>
