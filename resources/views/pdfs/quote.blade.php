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
        .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #fef3c7; color: #92400e; font-size: 10px; font-weight: bold; }
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
                <div class="title">QUOTATION</div>
                <div>{{ $quote->quote_number }}</div>
                <div class="muted">Issued: {{ ($quote->sent_at ?? now())->format('d M Y') }}</div>
                @if ($quote->valid_until)
                    <div class="muted">Valid Until: {{ $quote->valid_until->format('d M Y') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="header">
        <tr>
            <td>
                <div class="muted">Prepared For</div>
                <div><strong>{{ $quote->name }}</strong></div>
                <div>{{ $quote->email }}</div>
                @if ($quote->phone)<div>{{ $quote->phone }}</div>@endif
            </td>
        </tr>
    </table>

    @if ($quote->project_description)
        <p><strong>Project:</strong> {{ $quote->project_description }}</p>
    @endif

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
            @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->label }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format((float) $item->unit_price) }}</td>
                    <td class="text-right">{{ number_format((float) $item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">{{ number_format((float) $quote->subtotal) }}</td></tr>
        <tr><td>VAT ({{ $quote->vatRatePercent() }}%)</td><td class="text-right">{{ number_format((float) $quote->vat_amount) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="text-right">{{ number_format((float) $quote->total) }}</td></tr>
    </table>

    <div class="terms">
        <p><strong>Terms &amp; Conditions</strong></p>
        <p>
            This quotation is valid until the date shown above. Prices are quoted in {{ setting('tax.currency_code', 'TZS') }} and include {{ $quote->vatRatePercent() }}% VAT.
            Availability of materials is subject to stock levels at time of order confirmation. Payment terms and delivery arrangements will be
            confirmed upon acceptance of this quote.
        </p>
    </div>
</body>
</html>
