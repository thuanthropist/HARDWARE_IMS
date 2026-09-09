<?php

namespace App\Providers;

use App\Models\CalculatorType;
use App\Models\Department;
use App\Services\CartService;
use App\Services\SettingsManager;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // The default RedirectIfAuthenticated always sends already-logged-in
        // users to the admin /dashboard, regardless of which guard fired it —
        // that would bounce an already-logged-in customer hitting /register
        // or /account/login into the staff-only admin panel (and then
        // straight back out to the staff login screen) instead of the
        // storefront they were already using. Routed off the request's own
        // route name (not "is the customer guard authenticated") so it stays
        // correct even for someone who happens to hold both a staff and a
        // customer session in the same browser.
        RedirectIfAuthenticated::redirectUsing(fn (Request $request): string => $request->routeIs('storefront.register', 'storefront.login')
            ? route('storefront.home')
            : route('dashboard'));

        // Guarded by hasTable() since this runs on every boot, including
        // artisan commands (migrate, tinker) executed before the settings
        // table exists yet on a fresh install.
        if (Schema::hasTable('settings')) {
            $timezone = setting('business.timezone');

            if ($timezone) {
                date_default_timezone_set($timezone);
                config(['app.timezone' => $timezone]);
            }

            $whatsappNumber = setting('whatsapp.number');

            if ($whatsappNumber) {
                config(['services.whatsapp.number' => $whatsappNumber]);
            }
        }

        View::composer('components.layouts.storefront', function ($view): void {
            $cart = app(CartService::class)->peek();
            $cart?->load('items.product');

            $view->with([
                'navDepartments' => Department::active()->orderBy('sort_order')->get(),
                'navCalculatorTypes' => CalculatorType::active()->orderBy('name')->get(),
                'cartItemCount' => (int) ($cart?->items->sum('quantity') ?? 0),
                'cartPreview' => $cart,
            ]);
        });
    }
}
