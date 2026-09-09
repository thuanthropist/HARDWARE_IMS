<x-layouts.storefront :title="'Order '.$order->order_number">
    <div class="container" style="padding-top: 7.5rem; max-width: 760px;">
        <a href="{{ route('storefront.account.orders') }}" class="small mb-3 d-inline-block">&larr; Back to My Orders</a>

        <div class="d-flex justify-content-between align-items-start mb-4" data-aos="fade-up">
            <div>
                <h1 class="fw-bold mb-1">{{ $order->order_number }}</h1>
                <p class="text-body-secondary mb-0">Placed {{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="fw-bold fs-5" style="color: var(--sf-primary);">TZS {{ number_format((float) $order->total, 0) }}</span>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="80">
            <x-storefront.order-status-timeline :order="$order" />
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="120">
            <h6 class="fw-bold mb-3">Items</h6>
            @foreach ($order->items as $item)
                <div class="d-flex justify-content-between small mb-2">
                    <span>{{ $item->quantity }}&times; {{ $item->product_name }}</span>
                    <span class="fw-semibold">TZS {{ number_format((float) $item->line_total, 0) }}</span>
                </div>
            @endforeach
            <hr>
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-body-secondary">Subtotal</span>
                <span>TZS {{ number_format((float) $order->subtotal, 0) }}</span>
            </div>
            <div class="d-flex justify-content-between small">
                <span class="text-body-secondary">VAT ({{ $order->vatRatePercent() }}%)</span>
                <span>TZS {{ number_format((float) $order->vat_amount, 0) }}</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="160">
            <h6 class="fw-bold mb-2">Delivery</h6>
            <p class="small mb-0">
                {{ $order->delivery_method === 'pickup' ? 'Pickup at: '.($order->warehouse->name ?? 'selected warehouse') : 'Delivery to: '.$order->delivery_address }}
            </p>
        </div>

        @if (! in_array($order->status, ['pending_confirmation', 'rejected'], true))
            <a href="{{ route('storefront.account.orders.pdf', $order->order_number) }}" class="btn btn-outline-primary w-100" data-aos="fade-up">
                Download Invoice PDF
            </a>
        @endif
    </div>
</x-layouts.storefront>
