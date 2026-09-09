<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1e2433;">
    <h2>Thanks for your order, {{ $order->name }}!</h2>
    <p>We've received your order <strong>{{ $order->order_number }}</strong> and it's pending confirmation from our team. We'll be in touch shortly to confirm availability and next steps.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid #e5e7eb;">
                <th style="padding: 6px 0;">Item</th>
                <th style="padding: 6px 0; text-align: right;">Qty</th>
                <th style="padding: 6px 0; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr style="border-bottom: 1px solid #f1f3f6;">
                    <td style="padding: 6px 0;">{{ $item->product_name }}</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $item->quantity }}</td>
                    <td style="padding: 6px 0; text-align: right;">{{ number_format((float) $item->line_total) }} TZS</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 16px;">
        Subtotal: {{ number_format((float) $order->subtotal) }} TZS<br>
        VAT ({{ $order->vatRatePercent() }}%): {{ number_format((float) $order->vat_amount) }} TZS<br>
        <strong>Total: {{ number_format((float) $order->total) }} TZS</strong>
    </p>

    <p>{{ $order->delivery_method === 'pickup' ? 'You chose in-store pickup.' : 'Delivery address: '.$order->delivery_address }}</p>

    <p>— Hardware IMS</p>
</body>
</html>
