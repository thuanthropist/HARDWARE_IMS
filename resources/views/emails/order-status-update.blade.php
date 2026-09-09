<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1e2433;">
    <h2>{{ $headline }}</h2>
    <p>Hi {{ $order->name }},</p>
    <p>{{ $body }}</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid #e5e7eb;">
                <th style="padding: 6px 0;">Item</th>
                <th style="padding: 6px 0; text-align: right;">Ordered</th>
                <th style="padding: 6px 0; text-align: right;">Fulfilled</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr style="border-bottom: 1px solid #f1f3f6;">
                    <td style="padding: 6px 0;">{{ $item->product_name }}</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $item->quantity }}</td>
                    <td style="padding: 6px 0; text-align: right;">{{ $item->fulfilled_quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 16px;">Order Number: <strong>{{ $order->order_number }}</strong></p>

    <p>— Hardware IMS</p>
</body>
</html>
