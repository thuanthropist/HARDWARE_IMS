<?php

namespace Mandray\LicenseClient\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Mandray\LicenseClient\Facades\License;
use Mandray\LicenseClient\Services\TokenStore;
use Mandray\LicenseClient\Tests\Support\FakeServerSigner;
use Mandray\LicenseClient\Tests\TestCase;

class CheckLicenseFeatureMiddlewareTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::middleware(['web', 'license.feature:ai_assistant'])
            ->get('/ai-assistant', fn () => 'ai-ok');
    }

    public function test_aborts_403_when_feature_is_not_on_the_license(): void
    {
        $this->activeTokenWithFeatures(['ocr']);

        $this->get('/ai-assistant')->assertForbidden();
    }

    public function test_passes_through_when_feature_is_on_the_license(): void
    {
        $this->activeTokenWithFeatures(['ocr', 'ai_assistant']);

        $this->get('/ai-assistant')->assertOk()->assertSee('ai-ok');
    }

    private function activeTokenWithFeatures(array $features): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => $signer->publicKeyBase64]);

        $token = $signer->issue([
            'license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD',
            'expires_at' => now()->addYear()->toIso8601String(),
            'features_enabled' => $features,
        ]);

        app(TokenStore::class)->write($token);
        License::forgetCache();
    }
}
