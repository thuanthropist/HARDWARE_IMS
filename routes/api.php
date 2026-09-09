<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CalculatorApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Calculator API
|--------------------------------------------------------------------------
|
| Unauthenticated by design — these back the Phase 4 storefront's public
| "Smart Planning Tools" pages, used by guest and logged-in customers alike.
| Rate-limited via the default "api" throttle middleware.
|
*/

Route::get('calculators', [CalculatorApiController::class, 'index']);
Route::get('calculators/{calculatorType:key}', [CalculatorApiController::class, 'show']);
Route::post('calculators/{calculatorType:key}/calculate', [CalculatorApiController::class, 'calculate']);
Route::post('calculator-submissions/{calculatorSubmission}/cart-items', [CalculatorApiController::class, 'cartItems']);
