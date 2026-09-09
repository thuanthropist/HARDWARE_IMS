<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CustomerLoginRequest;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerLoginController extends Controller
{
    public function create(): View
    {
        return view('storefront.auth.login');
    }

    public function store(CustomerLoginRequest $request, CartService $cartService): RedirectResponse
    {
        $sessionId = $request->session()->getId();

        $request->authenticate();
        $request->session()->regenerate();

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $cartService->mergeGuestCartIntoCustomer($customer, $sessionId);

        return redirect()->intended(route('storefront.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.home');
    }
}
