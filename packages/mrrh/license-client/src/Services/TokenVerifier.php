<?php

namespace Mrrh\LicenseClient\Services;

use Illuminate\Support\Facades\Log;
use SodiumException;

/**
 * Verifies the JWT-like `header.payload.signature` tokens issued by the License
 * Server's LicenseSigningService. Mirrors that service's exact base64url +
 * signing-input scheme so tokens verify byte-for-byte the same way the server
 * signed them.
 */
class TokenVerifier
{
    /**
     * Verify a token's signature against the configured public key and return
     * its decoded claims, or null if the token is malformed, tampered with, or
     * signed with a key/algorithm we can't verify.
     */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerSegment, $payloadSegment, $signatureSegment] = $parts;

        $header = json_decode($this->base64UrlDecode($headerSegment), true);

        if (! is_array($header) || ! isset($header['alg'])) {
            return null;
        }

        $signingInput = "{$headerSegment}.{$payloadSegment}";
        $signature = $this->base64UrlDecode($signatureSegment);

        if (! $this->verifySignature((string) $header['alg'], $signingInput, $signature)) {
            return null;
        }

        $claims = json_decode($this->base64UrlDecode($payloadSegment), true);

        return is_array($claims) ? $claims : null;
    }

    protected function verifySignature(string $alg, string $signingInput, string $signature): bool
    {
        $publicKey = trim((string) config('license-client.public_key'));

        if ($publicKey === '') {
            Log::error('license-client: no public_key configured; cannot verify any license token.');

            return false;
        }

        return match ($alg) {
            'EdDSA' => $this->verifyEd25519($signingInput, $signature, $publicKey),
            'RS256' => $this->verifyRsa($signingInput, $signature, $publicKey),
            default => false,
        };
    }

    protected function verifyEd25519(string $data, string $signature, string $publicKeyBase64): bool
    {
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            Log::error('license-client: cannot verify an Ed25519 license token — the sodium PHP extension is not installed/enabled on this server.');

            return false;
        }

        $publicKey = base64_decode($publicKeyBase64, true);

        if ($publicKey === false || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return false;
        }

        try {
            return sodium_crypto_sign_verify_detached($signature, $data, $publicKey);
        } catch (SodiumException) {
            return false;
        }
    }

    protected function verifyRsa(string $data, string $signature, string $publicKeyPem): bool
    {
        $key = openssl_pkey_get_public($publicKeyPem);

        if ($key === false) {
            return false;
        }

        return openssl_verify($data, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    protected function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        $padded = $remainder ? str_pad($data, strlen($data) + (4 - $remainder), '=') : $data;

        return (string) base64_decode(strtr($padded, '-_', '+/'));
    }
}
