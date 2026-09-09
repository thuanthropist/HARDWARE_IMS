<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1e2433; }
        .header { text-align: center; margin-bottom: 16px; }
        .brand { font-size: 18px; font-weight: bold; color: #047857; }
        .muted { color: #6b7280; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th { background: #f8f9fb; text-align: left; padding: 6px; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        table.items td { padding: 6px; border-bottom: 1px solid #f1f3f6; }
        .text-right { text-align: right; }
        .totals { width: 260px; margin-left: auto; margin-top: 12px; }
        .totals td { padding: 3px 8px; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 1px solid #1e2433; }
        .meta { margin-top: 16px; font-size: 11px; }
        .meta td { padding: 2px 0; }
    </style>
</head>
<body>
    @php
        $logoPath = setting('business.logo_path') ? public_path('storage/'.setting('business.logo_path')) : null;
    @endphp
    <div class="header">
        @if ($logoPath && file_exists($logoPath))
            <img src="{{ $logoPath }}" style="height: 32px; margin-bottom: 4px;">
        @endif
        <div class="brand">{{ setting('business.name', 'Hardware IMS') }}</div>
        <div class="muted">{{ setting('business.address', 'Kariakoo, Dar es Salaam, Tanzania') }} &middot; {{ setting('business.phone', '+255 22 286 1000') }}</div>
        <div style="margin-top: 8px; font-weight: bold;">RECEIPT — {{ $sale->sale_number }}</div>
        <div class="muted">{{ $sale->created_at->format('d M Y H:i') }}</div>
    </div>

    <table class="meta">
        <tr><td class="muted" style="width: 140px;">Warehouse</td><td>{{ $sale->warehouse->name }}</td></tr>
        <tr><td class="muted">Cashier</td><td>{{ $sale->cashier->name ?? '—' }}</td></tr>
        @if ($sale->customer_name)
            <tr><td class="muted">Customer</td><td>{{ $sale->customer_name }} @if($sale->customer_phone) ({{ $sale->customer_phone }}) @endif</td></tr>
        @endif
        <tr><td class="muted">Payment Method</td><td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }} @if($sale->payment_reference) — {{ $sale->payment_reference }} @endif</td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format((float) $item->unit_price) }}</td>
                    <td class="text-right">{{ $item->discount_amount > 0 ? number_format((float) $item->discount_amount) : '—' }}</td>
                    <td class="text-right">{{ number_format((float) $item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">{{ number_format((float) $sale->subtotal) }}</td></tr>
        @if ($sale->discount_amount > 0)
            <tr><td>Discount</td><td class="text-right">-{{ number_format((float) $sale->discount_amount) }}</td></tr>
        @endif
        <tr><td>VAT ({{ $sale->vatRatePercent() }}%)</td><td class="text-right">{{ number_format((float) $sale->vat_amount) }}</td></tr>
        <tr class="grand"><td>Total ({{ setting('tax.currency_code', 'TZS') }})</td><td class="text-right">{{ number_format((float) $sale->total) }}</td></tr>
    </table>

    <p class="muted" style="text-align: center; margin-top: 24px;">Thank you for shopping with {{ setting('business.name', 'Hardware IMS') }}!</p>
</body>
</html>
