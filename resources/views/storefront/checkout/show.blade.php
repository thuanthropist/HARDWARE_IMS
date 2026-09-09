<x-layouts.storefront title="Checkout">
    <div class="container" style="padding-top: 7.5rem;">
        <h1 class="fw-bold mb-4" data-aos="fade-up">Checkout</h1>

        @if ($errors->any())
            <div class="alert alert-danger" data-aos="fade-up">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('storefront.checkout.store') }}" x-data="{ deliveryMethod: 'pickup' }">
            @csrf
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up">
                        <h6 class="fw-bold mb-3">Contact Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $customer?->name) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" name="email" value="{{ old('email', $customer?->email) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $customer?->phone) }}" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" data-aos="fade-up" data-aos-delay="80">
                        <h6 class="fw-bold mb-3">Delivery</h6>
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" value="pickup" id="pickup" x-model="deliveryMethod" checked>
                                <label class="form-check-label" for="pickup">In-store Pickup</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" value="delivery" id="delivery" x-model="deliveryMethod">
                                <label class="form-check-label" for="delivery">Delivery</label>
                            </div>
                        </div>

                        <div x-show="deliveryMethod === 'pickup'">
                            <label class="form-label small fw-semibold">Pickup Warehouse</label>
                            <select name="warehouse_id" class="form-select">
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }} — {{ $warehouse->location }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="deliveryMethod === 'delivery'" x-cloak>
                            <label class="form-label small fw-semibold">Delivery Address</label>
                            <textarea name="delivery_address" rows="3" class="form-control">{{ old('delivery_address') }}</textarea>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-up" data-aos-delay="160">
                        <label class="form-label small fw-semibold">Order Notes (optional)</label>
                        <textarea name="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4" data-aos="fade-up" style="position: sticky; top: 6.5rem;">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        @foreach ($cart->items as $item)
                            <div class="d-flex justify-content-between small mb-2">
                                <span>{{ $item->quantity }}&times; {{ $item->product->name }}</span>
                                <span class="fw-semibold">TZS {{ number_format($item->lineTotal, 0) }}</span>
                            </div>
                        @endforeach
                        <hr>
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
                        <button type="submit" class="btn btn-primary w-100">Place Order</button>
                        <p class="small text-body-secondary mt-3 mb-0">
                            Your order will be reviewed and confirmed by our team before dispatch — no payment is taken online.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.storefront>
