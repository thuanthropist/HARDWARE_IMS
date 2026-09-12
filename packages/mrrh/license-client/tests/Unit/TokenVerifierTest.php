<?php

namespace Mrrh\LicenseClient\Tests\Unit;

use Mrrh\LicenseClient\Services\TokenVerifier;
use Mrrh\LicenseClient\Tests\Support\FakeServerSigner;
use Mrrh\LicenseClient\Tests\TestCase;

class TokenVerifierTest extends TestCase
{
    public function test_verifies_a_correctly_signed_token(): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => $signer->publicKeyBase64]);

        $token = $signer->issue([
            'license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD',
            'product_code' => 'EOFF',
            'expires_at' => now()->addYear()->toIso8601String(),
            'features_enabled' => ['ocr'],
            'max_users' => 25,
            'domain' => 'client.example.tz',
        ]);

        $claims = app(TokenVerifier::class)->verify($token);

        $this->assertNotNull($claims);
        $this->assertSame('EOFF-AAAA-BBBB-CCCC-DDDD', $claims['license_key']);
        $this->assertSame(25, $claims['max_users']);
    }

    public function test_rejects_a_token_signed_by_a_different_key(): void
    {
        $signer = new FakeServerSigner();
        $otherSigner = new FakeServerSigner();

        // Public key configured does NOT match the key that signed the token.
        config(['license-client.public_key' => $otherSigner->publicKeyBase64]);

        $token = $signer->issue(['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD']);

        $this->assertNull(app(TokenVerifier::class)->verify($token));
    }

    public function test_rejects_a_tampered_payload(): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => $signer->publicKeyBase64]);

        $token = $signer->issue(['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD', 'max_users' => 5]);

        $parts = explode('.', $token);
        $tamperedPayload = rtrim(strtr(base64_encode('{"license_key":"EOFF-AAAA-BBBB-CCCC-DDDD","max_users":999999}'), '+/', '-_'), '=');
        $tampered = "{$parts[0]}.{$tamperedPayload}.{$parts[2]}";

        $this->assertNull(app(TokenVerifier::class)->verify($tampered));
    }

    public function test_rejects_malformed_tokens(): void
    {
        $verifier = app(TokenVerifier::class);

        $this->assertNull($verifier->verify('not-a-real-token'));
        $this->assertNull($verifier->verify('one.two'));
        $this->assertNull($verifier->verify(''));
    }

    public function test_rejects_when_no_public_key_is_configured(): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => '']);

        $token = $signer->issue(['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD']);

        $this->assertNull(app(TokenVerifier::class)->verify($token));
    }
}
