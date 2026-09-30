<?php

namespace Mandray\LicenseClient;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mandray\LicenseClient\Console\Commands\ActivateLicense;
use Mandray\LicenseClient\Console\Commands\HeartbeatLicense;
use Mandray\LicenseClient\Http\Middleware\CheckLicense;
use Mandray\LicenseClient\Http\Middleware\CheckLicenseFeature;
use Mandray\LicenseClient\Services\ApiClient;
use Mandray\LicenseClient\Services\ServerFingerprint;
use Mandray\LicenseClient\Services\TokenStore;
use Mandray\LicenseClient\Services\TokenVerifier;

class LicenseClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/license-client.php', 'license-client');

        $this->app->singleton(TokenStore::class, function () {
            return new TokenStore((string) config('license-client.token_path', storage_path('app/license/license.token')));
        });

        $this->app->singleton(TokenVerifier::class);
        $this->app->singleton(ServerFingerprint::class);
        $this->app->singleton(ApiClient::class);
        $this->app->singleton(LicenseManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/license-client.php' => config_path('license-client.php'),
        ], 'license-client-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'license-client');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::componentNamespace('Mandray\\LicenseClient\\View\\Components', 'license');

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('license.check', CheckLicense::class);
        $router->aliasMiddleware('license.feature', CheckLicenseFeature::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ActivateLicense::class,
                HeartbeatLicense::class,
            ]);
        }
    }
}
