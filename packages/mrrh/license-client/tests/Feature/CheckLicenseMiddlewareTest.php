<?php

namespace Mrrh\LicenseClient\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Mrrh\LicenseClient\Facades\License;
use Mrrh\LicenseClient\Services\TokenStore;
use Mrrh\LicenseClient\Tests\Support\FakeServerSigner;
use Mrrh\LicenseClient\Tests\TestCase;

class CheckLicenseMiddlewareTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::middleware(['web', 'license.check'])->get('/dashboard', fn () => 'dashboard-ok')->name('dashboard');
        Route::middleware(['web', 'license.check'])->get('/documents/1', fn () => 'document-ok')->name('documents.show');
    }

    public function test_redirects_to_blocked_page_when_not_activated(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('license-client.blocked'));
    }

    public function test_redirects_to_expired_page_when_hard_expired(): void
    {
        $this->activeToken(now()->subDays(30)->toIso8601String());
        config(['license-client.grace_period_days' => 5]);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('license-client.expired'));
    }

    public function test_expired_state_still_allows_exempt_routes(): void
    {
        $this->activeToken(now()->subDays(30)->toIso8601String());
        config([
            'license-client.grace_period_days' => 5,
            'license-client.expired_exempt_route_names' => ['documents.show'],
        ]);

        $response = $this->get('/documents/1');

        $response->assertOk()->assertSee('document-ok');
    }

    public function test_grace_state_passes_through(): void
    {
        $this->activeToken(now()->subDays(2)->toIso8601String());
        config(['license-client.grace_period_days' => 5]);

        $response = $this->get('/dashboard');

        $response->assertOk()->assertSee('dashboard-ok');
    }

    public function test_active_state_passes_through_silently(): void
    {
        $this->activeToken(now()->addDays(30)->toIso8601String());

        $response = $this->get('/dashboard');

        $response->assertOk()->assertSee('dashboard-ok');
    }

    private function activeToken(?string $expiresAt): void
    {
        $signer = new FakeServerSigner();
        config(['license-client.public_key' => $signer->publicKeyBase64]);

        $token = $signer->issue(['license_key' => 'EOFF-AAAA-BBBB-CCCC-DDDD', 'expires_at' => $expiresAt]);
        app(TokenStore::class)->write($token);
        License::forgetCache();
    }
}
