<x-layouts.storefront title="Order Placed">
    <div class="container text-center" style="padding-top: 8rem; max-width: 640px;">
        <div class="sf-fade-up">
            <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success" style="width: 4.5rem; height: 4.5rem; font-size: 2rem;">
                ✓
            </div>
            <h1 class="fw-bold mb-2">Order Placed</h1>
            <p class="text-body-secondary mb-4">
                Thanks, {{ $order->name }} — your order is pending confirmation. We'll reach out shortly to confirm availability and next steps.
            </p>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-start">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-body-secondary small">Order Number</span>
                    <span class="fw-bold fs-5" style="color: var(--sf-primary);">{{ $order->order_number }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-body-secondary small">Status</span>
                    <span class="sf-badge-stock sf-badge-low-stock">Pending Confirmation</span>
                </div>

                <hr>
                @foreach ($order->items as $item)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $item->quantity }}&times; {{ $item->product_name }}</span>
                        <span class="text-body-secondary">TZS {{ number_format((float) $item->line_total, 0) }}</span>
                    </div>
                @endforeach

                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>TZS {{ number_format((float) $order->total, 0) }}</span>
                </div>

                <hr>
                <p class="small mb-0">
                    {{ $order->delivery_method === 'pickup' ? 'Pickup at: '.($order->warehouse->name ?? 'selected warehouse') : 'Delivery to: '.$order->delivery_address }}
                </p>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('storefront.home') }}" class="btn btn-outline-primary">Continue Shopping</a>
                @auth('customer')
                    <a href="{{ route('storefront.account.orders') }}" class="btn btn-primary">View My Orders</a>
                @endauth
                <x-storefront.whatsapp-link
                    class="btn btn-success"
                    :message="'Hi! I just placed order '.$order->order_number.' and wanted to follow up.'"
                >
                    Chat with Us on WhatsApp
                </x-storefront.whatsapp-link>
            </div>
        </div>
    </div>
</x-layouts.storefront>
