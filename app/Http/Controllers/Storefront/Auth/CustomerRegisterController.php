<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CustomerRegisterRequest;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerRegisterController extends Controller
{
    public function create(): View
    {
        return view('storefront.auth.register');
    }

    public function store(CustomerRegisterRequest $request, CartService $cartService): RedirectResponse
    {
        $sessionId = $request->session()->getId();

        $customer = Customer::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone') ?: null,
            'password' => Hash::make($request->string('password')),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $cartService->mergeGuestCartIntoCustomer($customer, $sessionId);

        return redirect()->route('storefront.home')->with('success', 'Welcome to Hardware IMS!');
    }
}
