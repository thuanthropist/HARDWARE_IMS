<?php

namespace Mrrh\LicenseClient\Tests\Feature;

use Mrrh\LicenseClient\Facades\License;
use Mrrh\LicenseClient\Services\TokenStore;
use Mrrh\LicenseClient\Tests\Support\FakeServerSigner;
use Mrrh\LicenseClient\Tests\TestCase;

class LicenseManagerStatusTest extends TestCase
{
    private FakeServerSigner $signer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signer = new FakeServerSigner();
        config(['license-client.public_key' => $this->signer->publicKeyBase64]);
    }

    public function test_status_is_not_activated_when_no_token_exists(): void
    {
        $this->assertSame('not_activated', License::status());
        $this->assertFalse(License::isValid());
    }

    public function test_status_is_invalid_for_a_corrupt_token(): void
    {
        // Written unencrypted, so TokenStore::read() will fail to decrypt it.
        $path = config('license-client.token_path');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, 'garbage-not-encrypted');

        $this->assertSame('invalid', License::status());
        $this->assertFalse(License::isValid());
    }

    public function test_status_is_active_for_a_future_expiry(): void
    {
        $this->storeToken(['expires_at' => now()->addDays(30)->toIso8601String(), 'max_users' => 10, 'features_enabled' => ['ocr']]);

        $this->assertSame('active', License::status());
        $this->assertTrue(License::isValid());
        $this->assertFalse(License::isInGracePeriod());
        $this->assertSame(10, License::maxUsers());
        $this->assertTrue(License::hasFeature('ocr'));
        $this->assertFalse(License::hasFeature('ai_assistant'));
    }

    public function test_status_is_active_for_a_perpetual_license_with_no_expiry(): void
    {
        $this->storeToken(['expires_at' => null]);

        $this->assertSame('active', License::status());
        $this->assertNull(License::daysUntilExpiry());
    }

    public function test_status_is_grace_when_past_expiry_but_within_grace_period(): void
    {
        config(['license-client.grace_period_days' => 5]);
        $this->storeToken(['expires_at' => now()->subDays(2)->toIso8601String()]);

        $this->assertSame('grace', License::status());
        $this->assertTrue(License::isValid());
        $this->assertTrue(License::isInGracePeriod());
    }

    public function test_status_is_expired_when_past_the_grace_period(): void
    {
        config(['license-client.grace_period_days' => 5]);
        $this->storeToken(['expires_at' => now()->subDays(10)->toIso8601String()]);

        $this->assertSame('expired', License::status());
        $this->assertFalse(License::isValid());
        $this->assertFalse(License::isInGracePeriod());
    }

    public function test_status_is_cached_until_forget_cache_is_called(): void
    {
        $this->storeToken(['expires_at' => now()->addDays(30)->toIso8601String()]);
        $this->assertSame('active', License::status());

        // Corrupt the file directly — should still read "active" from cache.
        file_put_contents(config('license-client.token_path'), 'garbage');
        $this->assertSame('active', License::status());

        License::forgetCache();
        $this->assertSame('invalid', License::status());
    }

    private function storeToken(array $claims): void
    {
        $token = $this->signer->issue($claims);
        app(TokenStore::class)->write($token);
        License::forgetCache();
    }
}
