<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1e2433; }
        .header { width: 100%; margin-bottom: 24px; }
        .header td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; color: #047857; }
        .muted { color: #6b7280; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th { background: #f8f9fb; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        table.items td { padding: 8px; border-bottom: 1px solid #f1f3f6; }
        .text-right { text-align: right; }
        .totals { width: 260px; margin-left: auto; margin-top: 12px; }
        .totals td { padding: 4px 8px; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 1px solid #1e2433; }
        .terms { margin-top: 32px; font-size: 10px; color: #6b7280; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #dcfce7; color: #166534; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $logoPath = setting('business.logo_path') ? public_path('storage/'.setting('business.logo_path')) : null;
    @endphp
    <table class="header">
        <tr>
            <td style="width: 60%;">
                @if ($logoPath && file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height: 36px; margin-bottom: 4px;">
                @endif
                <div class="brand">{{ setting('business.name', 'Hardware IMS') }}</div>
                <div class="muted">{{ setting('business.address', 'Kariakoo, Dar es Salaam, Tanzania') }}</div>
                <div class="muted">{{ collect([setting('business.phone', '+255 22 286 1000'), setting('business.email', 'info@hardwareims.example')])->filter()->implode(' · ') }}</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="title">INVOICE / RECEIPT</div>
                <div>{{ $order->order_number }}</div>
                <div class="muted">Date: {{ $order->created_at->format('d M Y') }}</div>
                <div class="muted">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
            </td>
        </tr>
    </table>

    <table class="header">
        <tr>
            <td>
                <div class="muted">Billed To</div>
                <div><strong>{{ $order->name }}</strong></div>
                <div>{{ $order->email }}</div>
                @if ($order->phone)<div>{{ $order->phone }}</div>@endif
            </td>
            <td style="text-align: right;">
                <div class="muted">{{ $order->delivery_method === 'pickup' ? 'Pickup Location' : 'Delivery Address' }}</div>
                <div>{{ $order->delivery_method === 'pickup' ? ($order->warehouse->name ?? '—') : $order->delivery_address }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price (TZS)</th>
                <th class="text-right">Line Total (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name ?? $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format((float) $item->unit_price) }}</td>
                    <td class="text-right">{{ number_format((float) $item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">{{ number_format((float) $order->subtotal) }}</td></tr>
        <tr><td>VAT ({{ $order->vatRatePercent() }}%)</td><td class="text-right">{{ number_format((float) $order->vat_amount) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="text-right">{{ number_format((float) $order->total) }}</td></tr>
    </table>

    <div class="terms">
        <p><strong>Notes</strong></p>
        <p>
            Prices are quoted in {{ setting('tax.currency_code', 'TZS') }} and include {{ $order->vatRatePercent() }}% VAT. This document serves as your official
            receipt for goods confirmed under order {{ $order->order_number }}. Please retain it for your records.
        </p>
    </div>
</body>
</html>
