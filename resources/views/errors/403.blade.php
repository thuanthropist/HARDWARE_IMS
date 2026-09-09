<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feature Not Available &middot; {{ setting('business.name', 'Hardware IMS') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1e2433;
            padding: 1.5rem;
        }
        .card {
            max-width: 440px;
            width: 100%;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 45px rgba(17, 24, 39, 0.1);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .icon {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background: #ecfdf5;
            color: #047857;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.75rem;
        }
        h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
        }
        p {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0 0 1.5rem;
        }
        a.btn {
            display: inline-block;
            background: #f59e0b;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem 1.5rem;
            border-radius: 999px;
        }
        a.btn:hover { background: #d97706; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#9733;</div>
        <h1>This Feature Isn't in Your Plan</h1>
        <p>{{ $exception->getMessage() ?: 'This feature is not included in your current license plan.' }}</p>
        <a class="btn" href="{{ url('/') }}">Back to Home</a>
    </div>
</body>
</html>
