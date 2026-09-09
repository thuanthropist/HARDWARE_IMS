<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Resolves the "current" cart for a request — a logged-in customer's persistent
 * cart, or a session-scoped guest cart — and merges a guest cart into the
 * customer's cart on login.
 */
class CartService
{
    public function current(): Cart
    {
        if (Auth::guard('customer')->check()) {
            return Cart::firstOrCreate(['customer_id' => Auth::guard('customer')->id()]);
        }

        return Cart::firstOrCreate([
            'session_id' => Session::getId(),
            'customer_id' => null,
        ]);
    }

    /**
     * Look up the existing cart without creating one — used for read-only display
     * (e.g. the navbar item count) so anonymous visitors don't each get an empty
     * cart row just from viewing a page.
     */
    public function peek(): ?Cart
    {
        if (Auth::guard('customer')->check()) {
            return Cart::where('customer_id', Auth::guard('customer')->id())->first();
        }

        return Cart::where('session_id', Session::getId())->whereNull('customer_id')->first();
    }

    public function itemCount(): int
    {
        return (int) ($this->peek()?->items()->sum('quantity') ?? 0);
    }

    public function mergeGuestCartIntoCustomer(Customer $customer, string $sessionId): void
    {
        $guestCart = Cart::where('session_id', $sessionId)->whereNull('customer_id')->first();

        if (! $guestCart) {
            return;
        }

        $customerCart = Cart::firstOrCreate(['customer_id' => $customer->id]);

        foreach ($guestCart->items as $item) {
            $existing = $customerCart->items()
                ->where('product_variant_id', $item->product_variant_id)
                ->where('source', $item->source)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
                $item->delete();
            } else {
                $item->update(['cart_id' => $customerCart->id]);
            }
        }

        $guestCart->delete();
    }

    public function addItem(
        Cart $cart,
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        string $source = 'manual',
        ?int $calculatorSubmissionId = null,
        ?string $sourceLabel = null,
    ): CartItem {
        $unitPrice = (float) $product->selling_price + (float) ($variant?->additional_price ?? 0);

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->where('source', $source)
            ->where('calculator_submission_id', $calculatorSubmissionId)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);

            return $existing->fresh();
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'source' => $source,
            'calculator_submission_id' => $calculatorSubmissionId,
            'source_label' => $sourceLabel,
        ]);
    }
}
