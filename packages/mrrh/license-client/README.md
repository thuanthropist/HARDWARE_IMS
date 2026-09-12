# mrrh/license-client

Offline-first license activation and validation for Laravel apps that are
licensed through the central [License Server](../../../License%20MS) (Phase 1).
Install this into E-Office, HMIS, NetOps, and the Tourism App — each app
activates once against the server, then validates its stored token **locally**
on every request, only phoning home periodically via a heartbeat.

> **Note on credentials:** the phase brief called out three `.env` variables
> (`LICENSE_KEY`, `LICENSE_APP_SECRET`, `LICENSE_SERVER_URL`). This package
> needs a **fourth**, `LICENSE_API_KEY`, to match how the License Server's
> `/api/v1` endpoints actually authenticate callers (a per-installation
> `api_key` + `app_secret` pair, issued via **Admin → API Clients** on the
> License Server — see [Configuration](#configuration) below). Without it
> there's no way to identify which installation is calling before the HMAC
> signature is even checked.

## How it works

```
 First run:  php artisan license:activate EOFF-XXXX-XXXX-XXXX-XXXX
             ──────────────────────────────────────────────────────▶ License Server
             ◀── signed token (Ed25519), stored encrypted (APP_KEY) ──
             storage/app/license/license.token

 Every request:  CheckLicense middleware → LicenseManager
                  reads + verifies the LOCAL token (no network call)
                  → active / grace / expired / invalid / not_activated

 Periodically:  php artisan license:heartbeat  (weekly, via your scheduler)
                 ──────────────────────────────▶ License Server
                 ◀── status + maybe a refreshed token ──
                 on failure: logged, local token untouched — app keeps working
```

The signature check (Ed25519 via `sodium`, or RSA as a fallback) means a site
with no internet connectivity keeps validating correctly against its last
verified token until that token's own `expires_at` + grace period runs out.

## Installation

This is a private package, not on Packagist — require it via a local **path**
repository (recommended while developing all four apps side-by-side) or a
private VCS repository once it has its own git remote.

**Path repository** — add to the host app's `composer.json`:

```json
{
    "repositories": [
        { "type": "path", "url": "../../packages/mrrh/license-client" }
    ]
}
```

(adjust the relative path to wherever this package actually sits relative to
the host app)

Then:

```bash
composer require mrrh/license-client:@dev
```

Laravel's package discovery registers the service provider and the `License`
facade automatically — no manual entries needed in `bootstrap/providers.php`.

### Publish the config

```bash
php artisan vendor:publish --tag=license-client-config
```

This creates `config/license-client.php`. Edit two values that are fixed per
application (not per environment, so they're not `.env` vars):

```php
'product_code' => 'EOFF',              // must match this product's `code` on the License Server
'public_key'   => 'BASE64_ED25519_...', // from the License Server: Products → this product → signing key
```

### Configure `.env`

```env
LICENSE_SERVER_URL=https://licenses.mrrh.go.tz
LICENSE_KEY=EOFF-XXXX-XXXX-XXXX-XXXX
LICENSE_API_KEY=01J...                  # from License Server: Admin → API Clients → New API Client
LICENSE_APP_SECRET=***                  # shown once, alongside the API key, when you create it
```

`LICENSE_API_KEY` and `LICENSE_APP_SECRET` are generated together on the
License Server (**Admin → API Clients → New API Client**, select this
product). The secret is shown exactly once — store it straight into this
app's `.env`, never in git.

### `.gitignore`

The activated token is written to `storage/app/license/license.token`,
encrypted with this app's `APP_KEY`. It's installation-specific and must
never be committed. Laravel's default `storage/app/*` ignore rules already
cover it in most app skeletons — if yours doesn't, add:

```gitignore
/storage/app/license/
```

### Activate

```bash
php artisan license:activate EOFF-XXXX-XXXX-XXXX-XXXX
```

On success, the signed token is verified and stored. On failure (invalid key,
activation limit reached, license suspended/revoked/expired) you get a clear
error and nothing is written.

## Registering the middleware (Laravel 11 style)

`bootstrap/app.php`:

```php
use Mrrh\LicenseClient\Http\Middleware\CheckLicense;
use Mrrh\LicenseClient\Http\Middleware\CheckLicenseFeature;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'license.check' => CheckLicense::class,
        'license.feature' => CheckLicenseFeature::class,
    ]);

    // Apply to the whole authenticated app (adjust to your route structure):
    $middleware->web(append: [
        // do NOT put license.check here directly if you have guest-accessible
        // routes that must stay reachable even when unlicensed (e.g. the
        // login page) — see the integration checklist below instead.
    ]);
})
```

`license.check` **must** run as route/group middleware (after routing), not
as a global `web`-array entry that fires before the route is resolved — it
needs `$request->route()` to check the exempt-route list. Apply it to a route
group, e.g.:

```php
Route::middleware(['web', 'auth', 'license.check'])->group(function () {
    // your app's actual routes
});
```

Gate a specific feature behind the license plan:

```php
Route::middleware(['license.feature:ai_assistant'])->group(function () {
    // AI Assistant routes — 403s with a friendly message if the license
    // doesn't include the "ai_assistant" feature
});
```

## Scheduling the heartbeat

Laravel 11+ (no `Kernel.php`) — add one line to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('license:heartbeat')->weekly();
```

(older apps using `app/Console/Kernel.php`: add the same call inside
`schedule(Schedule $schedule)`.)

Run it more often (e.g. `daily()`) if the site has generally reliable
connectivity — heartbeat failures are silent and logged, so there's no harm
in trying more frequently.

## The grace-period banner

Drop this into your main authenticated layout, wherever a persistent banner
makes sense:

```blade
<x-license::grace-banner />
```

Renders nothing unless the license is in its grace period; dismissible via
Alpine.js (the component assumes Alpine is already loaded, as it is in all
four apps' EVA-styled layouts).

## The `License` facade

```php
use Mrrh\LicenseClient\Facades\License;

License::isValid();          // bool — active or grace
License::isInGracePeriod();  // bool
License::status();           // 'active' | 'grace' | 'expired' | 'invalid' | 'not_activated'
License::hasFeature('ocr');  // bool
License::maxUsers();         // ?int
License::expiresAt();        // ?Carbon
License::daysUntilExpiry();  // ?int — negative if already past expiry
```

Reading these is a local cache lookup + (at most, once every
`cache_ttl_minutes`) an Ed25519 signature check — safe to call from anywhere,
including Blade views and hot request paths.

## What `license:heartbeat` actually does to the local token

| Server reports | Local effect |
|---|---|
| `active`, with a new `signed_token` | Old token replaced, cache cleared |
| `active`, no `signed_token` (nothing changed) | Nothing — local token kept as-is |
| `suspended` or `revoked` | **Local token deleted** (see below) |
| Request fails / times out / connection error | Nothing — logged only, local token kept as-is |
| Response returned but fails signature or freshness check | Nothing — logged only, local token kept as-is (protects against a corrupted or replayed response silently downgrading trust) |

On `suspended`/`revoked` there's no legitimate replacement token to store —
the honest behavior is to drop trust in the old one immediately rather than
let a cryptographically-still-valid-but-now-untrusted token keep working
until its natural expiry. This is the one case where a successful heartbeat
can turn `active` into `not_activated` outright.

## `server_fingerprint` recipe

`sha256(hostname | machine-id | install path)` — see
[`src/Services/ServerFingerprint.php`](src/Services/ServerFingerprint.php) for
the exact implementation. Deliberately:

- **insensitive to** IP address changes and reboots (neither hostname nor
  machine-id nor install path depend on the network or uptime)
- **sensitive to** a genuine server migration (different hardware → different
  machine-id; a fresh install path → different fingerprint)

`machine-id` resolution order: Linux `/etc/machine-id` →
`/var/lib/dbus/machine-id` → Windows registry `MachineGuid` → primary disk
serial number → (if none of those are available) `php_uname()` as a last
resort, so activation never hard-fails purely because the fingerprint
sources are locked down on a given host.

## Security notes

- Every outgoing request carries `X-Api-Key`, `X-Timestamp`, and
  `X-Signature: hex hmac_sha256("{timestamp}.{raw JSON body}", app_secret)` —
  identical scheme to the License Server's `VerifyApiClient` middleware.
- Freshly-received tokens (from `activate`/`heartbeat`) are rejected if their
  `iat` claim is older than `response_max_age_minutes` (default 5) — a
  lightweight defence against a captured server response being replayed back
  to this app later. This does **not** affect ordinary offline validation of
  an already-stored token, whose `iat` is naturally old.
- The stored token is encrypted with this app's `APP_KEY`
  (`Illuminate\Support\Facades\Crypt`). Copying `license.token` to another
  server is useless without that server's exact `APP_KEY` too.

## Integration checklist (E-Office, HMIS, NetOps, Tourism App)

1. **Require the package**, publish the config, set `.env` (see above).
2. **Set `product_code`** in `config/license-client.php` to this app's code
   (`EOFF` / `HMIS` / `NETO` / `TOUR`) and paste in the matching `public_key`
   from the License Server.
3. **Apply `license.check`** to the app's authenticated route group — usually
   wherever `auth` middleware is already applied, e.g. in
   `routes/web.php`/`routes/admin.php`:
   ```php
   Route::middleware(['web', 'auth', 'license.check'])->group(function () {
       // ...
   });
   ```
   Do **not** apply it to the login/logout routes or any public marketing
   pages — a locked-out app must still let staff log in to see *why* it's
   locked (the blocked/expired pages don't require auth themselves, but
   users still need to reach them without bouncing through an auth wall
   first if `license.check` runs before `auth`; keep `license.check` **after**
   `auth` in the group as shown above so authenticated users get routed to
   the license pages, not anonymous ones).
4. **Decide the expired-state exempt routes** for this specific app and set
   `expired_exempt_route_names` / `expired_exempt_patterns` in
   `config/license-client.php`:
   - **E-Office**: exempt viewing/downloading already-created documents and
     their approval history; block creating new documents/workflows.
   - **HMIS**: exempt viewing existing patient records (continuity of care);
     block new registrations, billing, and pharmacy dispensing.
   - **NetOps**: exempt viewing existing monitoring dashboards/incident
     history; block acknowledging/creating new incidents or alert rules.
   - **Tourism App**: exempt viewing already-issued permits/bookings; block
     issuing new permits or taking new bookings.
5. **Add `<x-license::grace-banner />`** to each app's main authenticated
   layout (near the top, above the page content).
6. **Gate premium modules** (AI Assistant, OCR, etc.) with
   `license.feature:{key}` on their route groups, matching the feature keys
   configured on that product's plans in the License Server admin.
7. **Schedule the heartbeat** — one line in `routes/console.php`
   (`Schedule::command('license:heartbeat')->weekly();`).
8. **Activate** on first deploy: `php artisan license:activate <key>`.
9. **Confirm `.gitignore`** covers `storage/app/license/` before the first
   commit that includes an activated token.

## Testing this package

```bash
composer install
vendor/bin/phpunit
```

Uses Orchestra Testbench; no external services or real License Server needed
— tests generate their own throwaway Ed25519 key pairs and fake the HTTP
layer (`Http::fake()`).

## Not in this phase

Client-side renewal reminder emails/SMS and the usage analytics dashboard are
Phase 3, on the License Server side — this package only ever reports its
locally-known status; it doesn't send anything beyond the heartbeat.
