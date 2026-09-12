<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>License Required — {{ config('app.name', 'Application') }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        .card {
            max-width: 460px;
            width: 100%;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
            text-align: center;
            margin: 1rem;
        }
        .badge {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
        p { color: #64748b; font-size: .9rem; line-height: 1.6; margin: 0 0 1.5rem; }
        .support { background: #f8fafc; border-radius: 10px; padding: 1rem; font-size: .85rem; text-align: left; }
        .support div { margin-bottom: .25rem; }
        .support div:last-child { margin-bottom: 0; }
        .support strong { color: #334155; }
        a { color: #3366ff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h1>License Required</h1>
        <p>This application does not have an active license installed. Please contact your vendor to activate it before continuing.</p>
        <div class="support">
            <div><strong>{{ config('license-client.support.name', 'Support') }}</strong></div>
            @if ($email = config('license-client.support.email'))
                <div>Email: <a href="mailto:{{ $email }}">{{ $email }}</a></div>
            @endif
            @if ($phone = config('license-client.support.phone'))
                <div>Phone: <a href="tel:{{ $phone }}">{{ $phone }}</a></div>
            @endif
        </div>
    </div>
</body>
</html>
