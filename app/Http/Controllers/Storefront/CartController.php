<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CalculatorSubmission;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CalculatorEngine;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(CartService $cartService): View
    {
        $cart = $cartService->current();
        $cart->load(['items.product.department', 'items.productVariant']);

        $groups = $cart->items
            ->groupBy(fn (CartItem $item) => $item->source === 'calculator'
                ? ($item->source_label ?? 'From Planning Tool')
                : 'Manually Added')
            ->sortKeysUsing(fn ($a, $b) => $a === 'Manually Added' ? 1 : ($b === 'Manually Added' ? -1 : 0));

        return view('storefront.cart.show', [
            'cart' => $cart,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request, CartService $cartService): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()->active()->findOrFail($data['product_id']);
        $variant = $data['product_variant_id']
            ? ProductVariant::where('product_id', $product->id)->findOrFail($data['product_variant_id'])
            : $product->variants()->first();

        $cartService->addItem($cartService->current(), $product, $variant, (int) $data['quantity']);

        return back()->with('success', "{$product->name} added to your cart.");
    }

    public function update(Request $request, CartItem $cartItem, CartService $cartService): RedirectResponse
    {
        $this->authorizeCartItem($cartItem, $cartService);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem, CartService $cartService): RedirectResponse
    {
        $this->authorizeCartItem($cartItem, $cartService);

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    public function addCalculatorResults(
        CalculatorSubmission $calculatorSubmission,
        CartService $cartService,
        CalculatorEngine $engine,
    ): RedirectResponse {
        $calculatorSubmission->load('calculatorType');
        $cart = $cartService->current();

        foreach ($engine->toCartItems($calculatorSubmission) as $line) {
            $product = Product::find($line['product_id']);

            if (! $product) {
                continue;
            }

            $variant = $product->variants()->first();
            $quantity = max(1, (int) ceil((float) $line['quantity']));

            $cartService->addItem(
                $cart,
                $product,
                $variant,
                $quantity,
                source: 'calculator',
                calculatorSubmissionId: $calculatorSubmission->id,
                sourceLabel: 'From '.$calculatorSubmission->calculatorType->name,
            );
        }

        $calculatorSubmission->update(['converted_to_cart' => true]);

        return redirect()->route('storefront.cart.show')->with('success', 'Materials added to your cart.');
    }

    private function authorizeCartItem(CartItem $cartItem, CartService $cartService): void
    {
        abort_unless($cartItem->cart_id === $cartService->current()->id, 403);
    }
}
