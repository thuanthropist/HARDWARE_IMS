<?php

namespace Mrrh\LicenseClient\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Talks to the License Server's /api/v1 endpoints. Every request carries an
 * HMAC-SHA256 signature (X-Signature) over "{timestamp}.{raw JSON body}" using
 * this installation's app_secret, plus the timestamp itself (X-Timestamp) and
 * this installation's api_key (X-Api-Key) — matching the License Server's
 * VerifyApiClient middleware exactly.
 */
class ApiClient
{
    public function activate(string $licenseKey, string $domain, string $fingerprint, string $appVersion): ApiResult
    {
        return $this->post('/api/v1/activate', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'server_fingerprint' => $fingerprint,
            'app_version' => $appVersion,
        ]);
    }

    public function heartbeat(string $licenseKey, string $domain, string $fingerprint, string $appVersion): ApiResult
    {
        return $this->post('/api/v1/heartbeat', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'server_fingerprint' => $fingerprint,
            'app_version' => $appVersion,
        ]);
    }

    protected function post(string $path, array $payload): ApiResult
    {
        $baseUrl = rtrim((string) config('license-client.license_server_url'), '/');
        $apiKey = (string) config('license-client.api_key');
        $appSecret = (string) config('license-client.app_secret');

        if ($baseUrl === '' || $apiKey === '' || $appSecret === '') {
            return new ApiResult(false, 0, [], 'This app is not configured yet: LICENSE_SERVER_URL, LICENSE_API_KEY, and LICENSE_APP_SECRET must all be set in .env.');
        }

        // Signed exactly as sent: build the JSON once, sign it, then transmit
        // that same string as the raw body so client and server compute the
        // same HMAC over identical bytes.
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $appSecret);

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
                'X-Timestamp' => $timestamp,
                'X-Signature' => $signature,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout((int) config('license-client.http_timeout', 10))
                ->send('POST', $baseUrl.$path, ['body' => $body]);
        } catch (ConnectionException $e) {
            return new ApiResult(false, 0, [], "Could not reach the License Server: {$e->getMessage()}");
        }

        $decoded = $response->json();

        return new ApiResult($response->successful(), $response->status(), is_array($decoded) ? $decoded : []);
    }
}
