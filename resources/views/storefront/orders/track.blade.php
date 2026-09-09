<x-layouts.storefront title="Track Your Order">
    <div class="container" style="padding-top: 7.5rem; max-width: 740px;">
        <div class="text-center mb-4" data-aos="fade-up">
            <h1 class="fw-bold mb-2">Track Your Order</h1>
            <p class="text-body-secondary">Enter your order number and the email you used at checkout.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="80">
            <form method="POST" action="{{ route('storefront.orders.track.find') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Order Number</label>
                    <input type="text" name="order_number" value="{{ old('order_number') }}" class="form-control" placeholder="SO-2026-0001" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Track</button>
                </div>
            </form>
        </div>

        @if ($notFound)
            <div class="alert alert-warning" data-aos="fade-up">
                We couldn't find an order matching that number and email. Double-check and try again.
            </div>
        @endif

        @if ($order)
            <div class="sf-fade-up">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $order->order_number }}</h5>
                        <p class="text-body-secondary small mb-0">Placed {{ $order->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="fw-bold" style="color: var(--sf-primary);">TZS {{ number_format((float) $order->total, 0) }}</span>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <x-storefront.order-status-timeline :order="$order" />
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold mb-3">Items</h6>
                    @foreach ($order->items as $item)
                        <div class="d-flex justify-content-between small mb-2">
                            <span>{{ $item->quantity }}&times; {{ $item->product_name }}</span>
                            <span class="fw-semibold">TZS {{ number_format((float) $item->line_total, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.storefront>
