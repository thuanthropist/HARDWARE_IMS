<x-layouts.storefront title="My Orders">
    <div class="container" style="padding-top: 7.5rem;">
        <h1 class="fw-bold mb-4" data-aos="fade-up">My Orders</h1>

        @if ($orders->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center" data-aos="fade-up">
                <p class="mb-3">You haven't placed any orders yet.</p>
                <a href="{{ route('storefront.home') }}" class="btn btn-primary">Start Shopping</a>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 p-2" data-aos="fade-up">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-body-secondary text-uppercase">
                                <th class="ps-3">Order #</th>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="pe-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $order->order_number }}</td>
                                    <td class="small text-body-secondary d-none d-md-table-cell">{{ $order->created_at->format('d M Y') }}</td>
                                    <td><span class="sf-badge-stock sf-badge-low-stock">{{ Str::headline($order->status) }}</span></td>
                                    <td class="text-end fw-semibold">TZS {{ number_format((float) $order->total, 0) }}</td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('storefront.account.orders.show', $order->order_number) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </div>
</x-layouts.storefront>
