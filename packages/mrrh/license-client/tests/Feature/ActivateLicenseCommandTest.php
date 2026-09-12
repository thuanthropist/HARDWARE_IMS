<?php

namespace Mrrh\LicenseClient\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Mrrh\LicenseClient\Facades\License;
use Mrrh\LicenseClient\Tests\Support\FakeServerSigner;
use Mrrh\LicenseClient\Tests\TestCase;

class ActivateLicenseCommandTest extends TestCase
{
    public function test_activation_stores_a_verified_token(): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => $signer->publicKeyBase64]);

        $token = $signer->issue([
            'license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD',
            'expires_at' => now()->addYear()->toIso8601String(),
            'max_users' => 50,
            'features_enabled' => ['ocr'],
        ]);

        Http::fake([
            '*/api/v1/activate' => Http::response([
                'signed_token' => $token,
                'expires_at' => now()->addYear()->toIso8601String(),
                'features_enabled' => ['ocr'],
                'max_users' => 50,
            ], 200),
        ]);

        $this->artisan('license:activate', ['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD'])
            ->assertExitCode(0);

        $this->assertSame('active', License::status());
        $this->assertSame(50, License::maxUsers());
    }

    public function test_activation_reports_server_rejection(): void
    {
        Http::fake([
            '*/api/v1/activate' => Http::response(['message' => 'Activation limit reached for this license.'], 403),
        ]);

        $this->artisan('license:activate', ['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD'])
            ->expectsOutputToContain('Activation limit reached for this license.')
            ->assertExitCode(1);

        $this->assertSame('not_activated', License::status());
    }

    public function test_activation_refuses_to_store_a_token_that_fails_signature_verification(): void
    {
        $wrongSigner = new FakeServerSigner();
        $configuredSigner = new FakeServerSigner();
        config(['license-client.public_key' => $configuredSigner->publicKeyBase64]);

        $token = $wrongSigner->issue(['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD']);

        Http::fake([
            '*/api/v1/activate' => Http::response(['signed_token' => $token], 200),
        ]);

        $this->artisan('license:activate', ['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD'])
            ->assertExitCode(1);

        $this->assertSame('not_activated', License::status());
    }

    public function test_activation_refuses_a_stale_token(): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => $signer->publicKeyBase64]);

        $token = $signer->issue([
            'license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD',
            'iat' => now()->subMinutes(30)->timestamp,
        ]);

        Http::fake([
            '*/api/v1/activate' => Http::response(['signed_token' => $token], 200),
        ]);

        $this->artisan('license:activate', ['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD'])
            ->assertExitCode(1);

        $this->assertSame('not_activated', License::status());
    }
}
