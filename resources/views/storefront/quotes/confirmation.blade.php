<x-layouts.storefront title="Quote Requested">
    <div class="container text-center" style="padding-top: 8rem; max-width: 640px;">
        <div class="sf-fade-up">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width: 4.5rem; height: 4.5rem; font-size: 2rem;">
                ✓
            </div>
            <h1 class="fw-bold mb-2">Quote Request Received</h1>
            <p class="text-body-secondary mb-4">Thanks, {{ $quote->name }} — our team will review your request and get back to you shortly.</p>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-start">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-body-secondary small">Reference Number</span>
                    <span class="fw-bold fs-5" style="color: var(--sf-primary);">{{ $quote->quote_number }}</span>
                </div>

                @if ($quote->items->isNotEmpty())
                    <hr>
                    <p class="small fw-semibold mb-2">Items Requested</p>
                    @foreach ($quote->items as $item)
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $item->product->name }}</span>
                            <span class="text-body-secondary">Qty {{ $item->quantity }}</span>
                        </div>
                    @endforeach
                @endif

                @if ($quote->project_description)
                    <hr>
                    <p class="small fw-semibold mb-1">Project Description</p>
                    <p class="small text-body-secondary mb-0">{{ $quote->project_description }}</p>
                @endif
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('storefront.home') }}" class="btn btn-outline-primary">Continue Shopping</a>
                <x-storefront.whatsapp-link
                    class="btn btn-success"
                    :message="'Hi! I just requested quote '.$quote->quote_number.' and wanted to follow up.'"
                >
                    Chat with Us on WhatsApp
                </x-storefront.whatsapp-link>
            </div>
        </div>
    </div>
</x-layouts.storefront>
