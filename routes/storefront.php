<?php

declare(strict_types=1);

use App\Http\Controllers\Storefront\Auth\CustomerLoginController;
use App\Http\Controllers\Storefront\Auth\CustomerRegisterController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ContactController;
use App\Http\Controllers\Storefront\CustomerAccountController;
use App\Http\Controllers\Storefront\DepartmentBrowseController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\ProductDetailController;
use App\Http\Controllers\Storefront\QuoteRequestController;
use App\Http\Controllers\Storefront\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('storefront.home');

Route::get('/contact', [ContactController::class, 'index'])->name('storefront.contact');

Route::get('/departments/{department:slug}', [DepartmentBrowseController::class, 'show'])->name('storefront.departments.show');
Route::get('/products/{product:slug}', [ProductDetailController::class, 'show'])->name('storefront.products.show');

Route::middleware('license.feature:smart-calculators')->group(function (): void {
    Route::get('/tools/{calculatorType:key}', [ToolController::class, 'show'])->name('storefront.tools.show');
    Route::post('/tools/{calculatorType:key}/calculate', [ToolController::class, 'calculate'])->name('storefront.tools.calculate');
});

Route::get('/cart', [CartController::class, 'show'])->name('storefront.cart.show');
Route::post('/cart/items', [CartController::class, 'store'])->name('storefront.cart.items.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('storefront.cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('storefront.cart.items.destroy');
Route::post('/cart/add-calculator-results/{calculatorSubmission}', [CartController::class, 'addCalculatorResults'])
    ->name('storefront.cart.addCalculatorResults');

// /request-quote (not /quotes/request) to avoid colliding with the admin
// panel's own GET /quotes/{quote} quote-review route (both apps are mounted
// at the same root) — see the /account/login precedent for the same issue.
Route::get('/request-quote', [QuoteRequestController::class, 'create'])->name('storefront.quotes.create');
Route::post('/quotes', [QuoteRequestController::class, 'store'])->middleware('license.check')->name('storefront.quotes.store');
Route::get('/quotes/{quote:quote_number}/confirmation', [QuoteRequestController::class, 'confirmation'])
    ->name('storefront.quotes.confirmation');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('storefront.checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('license.check')->name('storefront.checkout.store');
Route::get('/orders/{order:order_number}/confirmation', [CheckoutController::class, 'confirmation'])
    ->name('storefront.orders.confirmation');

Route::get('/track-order', [OrderTrackingController::class, 'show'])->name('storefront.orders.track');
Route::post('/track-order', [OrderTrackingController::class, 'find'])->name('storefront.orders.track.find');

// Namespaced under /account to avoid colliding with the admin panel's own
// /login and /logout routes (both apps are mounted at the same root).
Route::middleware('guest:customer')->group(function (): void {
    Route::get('/register', [CustomerRegisterController::class, 'create'])->name('storefront.register');
    Route::post('/register', [CustomerRegisterController::class, 'store'])->name('storefront.register.store');
    Route::get('/account/login', [CustomerLoginController::class, 'create'])->name('storefront.login');
    Route::post('/account/login', [CustomerLoginController::class, 'store'])->name('storefront.login.store');
});

Route::post('/account/logout', [CustomerLoginController::class, 'destroy'])->name('storefront.logout');

Route::middleware('auth:customer')->prefix('account')->group(function (): void {
    Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('storefront.account.orders');
    Route::get('/orders/{order:order_number}', [CustomerAccountController::class, 'orderShow'])->name('storefront.account.orders.show');
    Route::get('/orders/{order:order_number}/pdf', [CustomerAccountController::class, 'orderPdf'])->name('storefront.account.orders.pdf');
    Route::get('/quotes', [CustomerAccountController::class, 'quotes'])->name('storefront.account.quotes');
    Route::get('/quotes/{quote:quote_number}', [CustomerAccountController::class, 'quoteShow'])->name('storefront.account.quotes.show');
    Route::get('/quotes/{quote:quote_number}/pdf', [CustomerAccountController::class, 'quotePdf'])->name('storefront.account.quotes.pdf');
});
