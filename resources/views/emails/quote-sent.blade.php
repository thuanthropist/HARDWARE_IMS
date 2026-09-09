<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1e2433;">
    <h2>Your Quote is Ready</h2>
    <p>Hi {{ $quote->name }},</p>
    <p>Thanks for your patience — we've put together a formal quote for your project. Please find it attached as a PDF.</p>

    <p>
        Quote Number: <strong>{{ $quote->quote_number }}</strong><br>
        Total: <strong>{{ number_format((float) $quote->total) }} TZS</strong><br>
        @if ($quote->valid_until)
            Valid Until: <strong>{{ $quote->valid_until->format('d M Y') }}</strong>
        @endif
    </p>

    <p>Reply to this email or contact us on WhatsApp if you have any questions or would like to proceed.</p>

    <p>— Hardware IMS</p>
</body>
</html>
