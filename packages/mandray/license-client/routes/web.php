<?php

use Illuminate\Support\Facades\Route;

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
