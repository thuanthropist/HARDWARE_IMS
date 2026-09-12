<?php

namespace Mrrh\LicenseClient\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Mrrh\LicenseClient\Facades\License;
use Mrrh\LicenseClient\Services\TokenStore;
use Mrrh\LicenseClient\Tests\Support\FakeServerSigner;
use Mrrh\LicenseClient\Tests\TestCase;

class HeartbeatLicenseCommandTest extends TestCase
{
    private FakeServerSigner $signer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signer = new FakeServerSigner();
        config(['license-client.public_key' => $this->signer->publicKeyBase64]);
    }

    public function test_heartbeat_does_nothing_when_never_activated(): void
    {
        Http::fake();

        $this->artisan('license:heartbeat')->assertExitCode(0);

        Http::assertNothingSent();
        $this->assertSame('not_activated', License::status());
    }

    public function test_network_failure_keeps_the_last_valid_local_token(): void
    {
        $this->storeToken(['expires_at' => now()->addYear()->toIso8601String()]);

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Could not resolve host.');
        });

        $this->artisan('license:heartbeat')->assertExitCode(0);

        // Still active — the network failure did not touch the stored token.
        $this->assertSame('active', License::status());
    }

    public function test_heartbeat_stores_a_fresh_token_when_returned(): void
    {
        $this->storeToken(['expires_at' => now()->addDays(5)->toIso8601String()]);

        $renewedToken = $this->signer->issue([
            'license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD',
            'expires_at' => now()->addYear()->toIso8601String(),
        ]);

        Http::fake([
            '*/api/v1/heartbeat' => Http::response(['status' => 'active', 'signed_token' => $renewedToken], 200),
        ]);

        $this->artisan('license:heartbeat')->assertExitCode(0);

        $this->assertGreaterThan(300, License::daysUntilExpiry());
    }

    public function test_heartbeat_invalidates_local_token_when_server_reports_revoked(): void
    {
        $this->storeToken(['expires_at' => now()->addYear()->toIso8601String()]);
        $this->assertSame('active', License::status());

        Http::fake([
            '*/api/v1/heartbeat' => Http::response(['status' => 'revoked'], 200),
        ]);

        $this->artisan('license:heartbeat')->assertExitCode(0);

        $this->assertSame('not_activated', License::status());
    }

    public function test_heartbeat_ignores_a_response_that_fails_signature_verification(): void
    {
        $this->storeToken(['expires_at' => now()->addDays(5)->toIso8601String()]);

        $otherSigner = new FakeServerSigner();
        $badToken = $otherSigner->issue(['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD', 'expires_at' => now()->addYear()->toIso8601String()]);

        Http::fake([
            '*/api/v1/heartbeat' => Http::response(['status' => 'active', 'signed_token' => $badToken], 200),
        ]);

        $this->artisan('license:heartbeat')->assertExitCode(0);

        // Original 5-day-out expiry should remain — the bad token was ignored.
        $this->assertLessThanOrEqual(5, License::daysUntilExpiry());
    }

    private function storeToken(array $claims): void
    {
        $claims += ['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD', 'domain' => 'client.example.tz'];
        $token = $this->signer->issue($claims);
        app(TokenStore::class)->write($token);
        License::forgetCache();
    }
}
