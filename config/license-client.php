<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Server connection
    |--------------------------------------------------------------------------
    |
    | license_key / api_key / app_secret are per-installation credentials —
    | keep them in .env, never commit them. license_key identifies *this*
    | license; api_key + app_secret authenticate this installation's requests
    | to the License Server (issued together under Admin -> API Clients on
    | the License Server when you set this app up).
    |
    */

    'license_server_url' => env('LICENSE_SERVER_URL', 'https://licenses.example.tz'),

    'license_key' => env('LICENSE_KEY'),
    'api_key' => env('LICENSE_API_KEY'),
    'app_secret' => env('LICENSE_APP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Product identity
    |--------------------------------------------------------------------------
    |
    | These are fixed per application (not per environment), so they live
    | here rather than in .env. product_code must match the product's `code`
    | on the License Server. public_key is that product's Ed25519 public key
    | (base64), copied from the License Server (license_signing_keys.public_key,
    | algorithm "ed25519") — or a PEM-formatted RSA public key if the server
    | fell back to RSA for this product. Never put a private key here.
    |
    */

    'product_code' => env('LICENSE_PRODUCT_CODE', 'HWSTORE'),

    'public_key' => env('LICENSE_PUBLIC_KEY', 'eW2mHhUzjaCZGL867L/sSKV8Cn+Sy/Bo46ooJ4KihEg='),

    'app_version' => env('LICENSE_APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Offline validation behaviour
    |--------------------------------------------------------------------------
    |
    | grace_period_days mirrors the default configured on the License Server
    | for this product's licenses, but it is enforced entirely client-side —
    | the signed token does not carry a grace period, so this is the value
    | that actually governs the "grace" window when a device is offline past
    | its license's expires_at.
    |
    | cache_ttl_minutes controls how long a verified status is cached before
    | the token's signature is re-checked (signature verification itself is
    | cheap, but this avoids doing it on every single request).
    |
    | response_max_age_minutes rejects a freshly-received signed token from
    | /activate or /heartbeat if its `iat` claim is older than this many
    | minutes — a lightweight defence against a captured/replayed server
    | response being fed back to this app later. It does NOT affect ordinary
    | offline validation of an already-stored token (whose iat is naturally
    | old) — see LicenseManager, which only applies expires_at + grace there.
    |
    */

    'grace_period_days' => env('LICENSE_GRACE_PERIOD_DAYS', 5),

    'cache_ttl_minutes' => env('LICENSE_CACHE_TTL_MINUTES', 60),

    'response_max_age_minutes' => 5,

    'http_timeout' => 10,

    /*
    |--------------------------------------------------------------------------
    | Local token storage
    |--------------------------------------------------------------------------
    |
    | The signed token is stored encrypted (via APP_KEY) at this path. Add it
    | to .gitignore — see the package README.
    |
    */

    'token_path' => storage_path('app/license/license.token'),

    /*
    |--------------------------------------------------------------------------
    | Routes shown by the license.check middleware
    |--------------------------------------------------------------------------
    |
    | The package registers its own "blocked" and "expired" pages under these
    | route names. Point these at your own routes/views instead if you want
    | full control over the branding — the middleware only cares about the
    | route *name*, not which package/app defines it.
    |
    */

    'routes' => [
        'blocked' => 'license.blocked',
        'expired' => 'license.expired',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes that stay reachable once a license is hard-expired
    |--------------------------------------------------------------------------
    |
    | e.g. viewing/downloading already-generated documents, but not creating
    | new ones. Matched against the current route's name and/or URI pattern.
    |
    */

    'expired_exempt_route_names' => [
        'license.index',
        'license.activate',
        'storefront.account.orders.pdf',
        'storefront.account.quotes.pdf',
    ],

    'expired_exempt_patterns' => [
        // 'documents/*/download',
    ],

    /*
    |--------------------------------------------------------------------------
    | Support contact shown on the blocked/expired pages
    |--------------------------------------------------------------------------
    */

    'support' => [
        'name' => env('LICENSE_SUPPORT_NAME', 'Your Vendor'),
        'email' => env('LICENSE_SUPPORT_EMAIL'),
        'phone' => env('LICENSE_SUPPORT_PHONE'),
    ],

];
