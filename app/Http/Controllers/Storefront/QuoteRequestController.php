<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\QuoteRequestRequest;
use App\Models\CalculatorSubmission;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Notifications\NewPendingQuoteNotification;
use App\Services\CalculatorEngine;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class QuoteRequestController extends Controller
{
    public function create(CartService $cartService, CalculatorEngine $engine): View
    {
        $calculatorSubmission = null;
        $items = [];

        if (request()->filled('submission')) {
            $calculatorSubmission = CalculatorSubmission::with('calculatorType')->find(request()->integer('submission'));

            if ($calculatorSubmission) {
                $items = collect($engine->toCartItems($calculatorSubmission))->map(fn (array $line): array => [
                    'product_id' => $line['product_id'],
                    'name' => $line['name'],
                    'quantity' => max(1, (int) ceil((float) $line['quantity'])),
                ])->values()->all();
            }
        } elseif (request()->filled('product')) {
            $product = Product::active()->where('slug', request()->string('product'))->first();

            if ($product) {
                $items = [['product_id' => $product->id, 'name' => $product->name, 'quantity' => 1]];
            }
        } else {
            $cart = $cartService->peek();

            if ($cart) {
                $cart->load('items.product');

                $items = $cart->items->map(fn ($item): array => [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                ])->values()->all();
            }
        }

        $customer = auth('customer')->user();

        return view('storefront.quotes.create', [
            'items' => $items,
            'calculatorSubmission' => $calculatorSubmission,
            'customer' => $customer,
        ]);
    }

    public function store(QuoteRequestRequest $request): RedirectResponse
    {
        $quote = DB::transaction(function () use ($request): Quote {
            $quote = Quote::create([
                'quote_number' => Quote::generateQuoteNumber(),
                'customer_id' => auth('customer')->id(),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'source' => $request->filled('calculator_submission_id') ? 'calculator' : 'manual',
                'calculator_submission_id' => $request->input('calculator_submission_id') ?: null,
                'project_description' => $request->validated('project_description'),
                'preferred_contact_method' => $request->validated('preferred_contact_method'),
                'timeline' => $request->validated('timeline'),
            ]);

            foreach ($request->input('items', []) as $item) {
                if (empty($item['product_id'])) {
                    continue;
                }

                $quote->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                ]);
            }

            if ($quote->calculator_submission_id) {
                CalculatorSubmission::where('id', $quote->calculator_submission_id)
                    ->update(['converted_to_quote_id' => $quote->id]);
            }

            return $quote;
        });

        if (setting('notifications.pending_quote_enabled', true)) {
            $staff = User::role(setting('notifications.pending_quote_roles', ['Admin', 'Manager']))->get();
            if ($staff->isNotEmpty()) {
                Notification::send($staff, new NewPendingQuoteNotification($quote));
            }
        }

        return redirect()->route('storefront.quotes.confirmation', $quote);
    }

    public function confirmation(Quote $quote): View
    {
        $quote->load(['items.product', 'calculatorSubmission.calculatorType']);

        return view('storefront.quotes.confirmation', ['quote' => $quote]);
    }
}
