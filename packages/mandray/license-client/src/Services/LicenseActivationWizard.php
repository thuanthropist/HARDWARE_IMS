<?php

namespace Mandray\LicenseClient\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Breaks license activation into independently checkable steps, each
 * returning a plain {ok, message} result — driven by the activation-wizard
 * Blade partial (see resources/views/activation-wizard.blade.php) so an
 * admin sees exactly which step failed and why, instead of one opaque
 * "activation failed" message.
 */
class LicenseActivationWizard
{
    public function __construct(
        private readonly ApiClient $api,
        private readonly TokenVerifier $verifier,
        private readonly TokenStore $store,
        private readonly ServerFingerprint $fingerprint,
    ) {
    }

    /**
     * Step 1: .env has the three credentials this install needs, and they
     * aren't stuck in a stale config cache.
     */
    public function checkConfig(?string $licenseKey = null): array
    {
        return $this->reported($licenseKey, 'config', $this->runConfigCheck());
    }

    private function runConfigCheck(): array
    {
        $missing = collect([
            'LICENSE_SERVER_URL' => config('license-client.license_server_url'),
            'LICENSE_API_KEY' => config('license-client.api_key'),
            'LICENSE_APP_SECRET' => config('license-client.app_secret'),
        ])->filter(fn ($value) => blank($value))->keys();

        if ($missing->isNotEmpty()) {
            return [
                'ok' => false,
                'message' => 'Missing: '.$missing->implode(', ').'. Set these in .env, then run php artisan config:clear.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Server URL, API key, and app secret are all configured.',
        ];
    }

    /**
     * Step 2: this server can actually verify whichever algorithm the
     * License Server signs tokens with. This is the exact check that would
     * have caught the "sodium not installed" failure mode up front instead
     * of after a confusing "signature did not verify" message.
     */
    public function checkCrypto(?string $licenseKey = null): array
    {
        return $this->reported($licenseKey, 'crypto', $this->runCryptoCheck());
    }

    private function runCryptoCheck(): array
    {
        $hasSodium = function_exists('sodium_crypto_sign_verify_detached');
        $hasOpenssl = function_exists('openssl_verify');

        if ($hasSodium && $hasOpenssl) {
            return ['ok' => true, 'message' => 'Both Ed25519 (sodium) and RSA (openssl) verification are available.'];
        }

        if ($hasSodium) {
            return ['ok' => true, 'message' => 'Ed25519 (sodium) verification is available. (openssl is missing, but that only matters if this product falls back to RSA.)'];
        }

        if ($hasOpenssl) {
            return ['ok' => true, 'message' => 'RSA (openssl) verification is available. Ed25519 (sodium) is NOT — activation will fail if this product signs with Ed25519.', 'warning' => true];
        }

        return [
            'ok' => false,
            'message' => 'Neither the sodium nor openssl PHP extension is available on this server — no signed license token can be verified at all. Enable one of them in php.ini and restart the web server.',
        ];
    }

    /**
     * Step 3: the License Server is actually reachable from here, before we
     * spend a real activation attempt finding that out.
     */
    public function checkConnectivity(?string $licenseKey = null): array
    {
        $result = $this->runConnectivityCheck();

        // If the server couldn't be reached there's nobody to report to.
        return $result['ok'] ? $this->reported($licenseKey, 'connectivity', $result) : $result;
    }

    private function runConnectivityCheck(): array
    {
        $url = rtrim((string) config('license-client.license_server_url'), '/');

        if ($url === '') {
            return ['ok' => false, 'message' => 'LICENSE_SERVER_URL is not set.'];
        }

        try {
            $response = Http::timeout(8)->post($url.'/api/v1/verify', []);
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => "Could not reach {$url}: {$e->getMessage()}"];
        }

        // Any response in this range (even a 401 for a missing signature)
        // proves the host is up and speaking the expected API — a 5xx or no
        // response at all means it isn't.
        if ($response->status() >= 200 && $response->status() < 500) {
            return ['ok' => true, 'message' => "Reached the License Server at {$url} (HTTP {$response->status()})."];
        }

        return ['ok' => false, 'message' => "The License Server responded with HTTP {$response->status()}."];
    }

    /**
     * Step 4: the real activation call, using the same ApiClient/TokenVerifier/
     * TokenStore path as `php artisan license:activate`.
     */
    public function activate(string $licenseKey): array
    {
        $domain = $this->domain();
        $appVersion = (string) config('license-client.app_version', '1.0.0');

        $result = $this->api->activate($licenseKey, $domain, $this->fingerprint->generate(), $appVersion);

        if (! $result->ok) {
            return ['ok' => false, 'message' => 'Activation failed: '.$result->message()];
        }

        $token = $result->body['signed_token'] ?? null;

        if (! is_string($token) || $token === '') {
            return ['ok' => false, 'message' => 'The License Server did not return a signed token.'];
        }

        $claims = $this->verifier->verify($token);

        if ($claims === null) {
            return $this->reported($licenseKey, 'verify', [
                'ok' => false,
                'message' => "Received a token but its signature did not verify against the configured public key. Double-check config/license-client.php's public_key matches this product on the License Server.",
            ]);
        }

        $this->store->write($token);
        $this->reported($licenseKey, 'verify', ['ok' => true, 'message' => 'Token signature verified against the configured public key.']);

        return [
            'ok' => true,
            'message' => 'License activated and verified.',
            'expires_at' => $claims['expires_at'] ?? null,
            'features_enabled' => $claims['features_enabled'] ?? [],
        ];
    }

    private function domain(): string
    {
        return request()->getHost() ?: (string) config('app.url');
    }

    /**
     * Reports a step's outcome to the License Server (best-effort) and hands
     * the result back unchanged. No license key means nothing to attach it to.
     */
    private function reported(?string $licenseKey, string $step, array $result): array
    {
        if (blank($licenseKey)) {
            return $result;
        }

        $status = ! $result['ok'] ? 'error' : (($result['warning'] ?? false) ? 'warn' : 'ok');

        try {
            $this->api->reportProgress($licenseKey, $this->domain(), $step, $status, $result['message'] ?? null);
        } catch (Throwable) {
            // Reporting is advisory only — never let it affect the step itself.
        }

        return $result;
    }
}
