<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1e2433;">
    <h2>Your Quote Expires Soon</h2>
    <p>Hi {{ $quote->name }},</p>
    <p>
        Just a reminder — your quote <strong>{{ $quote->quote_number }}</strong> for
        <strong>{{ number_format((float) $quote->total) }} TZS</strong> is valid until
        <strong>{{ $quote->valid_until->format('d M Y') }}</strong>.
    </p>
    <p>If you'd like to proceed, reply to this email or reach out to us on WhatsApp before it expires.</p>
    <p>— Hardware IMS</p>
</body>
</html>
