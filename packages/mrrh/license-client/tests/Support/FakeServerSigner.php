<?php

namespace Mrrh\LicenseClient\Tests\Support;

/**
 * Stands in for the License Server's LicenseSigningService in tests — produces
 * tokens in the exact same `header.payload.signature` (base64url) shape, signed
 * with a freshly generated Ed25519 key pair, so we can test TokenVerifier
 * without depending on the server's own codebase.
 */
class FakeServerSigner
{
    public readonly string $publicKeyBase64;

    private readonly string $secretKey;

    public function __construct()
    {
        $keyPair = sodium_crypto_sign_keypair();
        $this->secretKey = sodium_crypto_sign_secretkey($keyPair);
        $this->publicKeyBase64 = base64_encode(sodium_crypto_sign_publickey($keyPair));
    }

    public function issue(array $claims): string
    {
        $header = ['alg' => 'EdDSA', 'typ' => 'LMS', 'kid' => 1];
        $claims += ['iat' => time()];

        $headerSegment = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadSegment = $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));
        $signingInput = "{$headerSegment}.{$payloadSegment}";

        $signature = sodium_crypto_sign_detached($signingInput, $this->secretKey);

        return "{$signingInput}.".$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
