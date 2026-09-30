<?php

use Illuminate\Support\Facades\Route;
use Mandray\LicenseClient\Http\Controllers\ActivationWizardController;

/*
|--------------------------------------------------------------------------
| License Server client — package routes
|--------------------------------------------------------------------------
|
| Self-contained pages the CheckLicense middleware redirects to. Route names
| (not these URIs) are what the middleware actually looks up, via
| config('license-client.routes.*') — point that config at your own routes
| instead if you want full control over the branding.
|
*/

Route::middleware('web')->group(function () {
    Route::view('/license/required', 'license-client::blocked')
        ->name('license-client.blocked');

    Route::view('/license/expired', 'license-client::expired')
        ->name('license-client.expired');
});

/*
|--------------------------------------------------------------------------
| Activation wizard — JSON step endpoints
|--------------------------------------------------------------------------
|
| Backs the activation-wizard Blade partial (@include it from your own
| Settings -> License page). Gated by config('license-client.wizard.middleware')
| — defaults to ['web', 'auth'] since this reveals server connectivity/config
| details and can trigger a real activation; set license-client.wizard.enabled
| to false to drop these routes entirely.
|
*/
if (config('license-client.wizard.enabled', true)) {
    Route::middleware(config('license-client.wizard.middleware', ['web', 'auth']))
        ->prefix('license/wizard')
        ->name('license-client.wizard.')
        ->group(function () {
            Route::get('check-config', [ActivationWizardController::class, 'checkConfig'])->name('check-config');
            Route::get('check-crypto', [ActivationWizardController::class, 'checkCrypto'])->name('check-crypto');
            Route::post('check-connectivity', [ActivationWizardController::class, 'checkConnectivity'])->name('check-connectivity');
            Route::post('activate', [ActivationWizardController::class, 'activate'])->name('activate');
        });
}
