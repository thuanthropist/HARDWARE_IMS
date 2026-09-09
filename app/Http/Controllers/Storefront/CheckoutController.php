<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\NewPendingOrderNotification;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(CartService $cartService): View|RedirectResponse
    {
        $cart = $cartService->current();
        $cart->load(['items.product', 'items.productVariant']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('storefront.cart.show')->with('error', 'Your cart is empty.');
        }

        $customer = auth('customer')->user();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('storefront.checkout.show', [
            'cart' => $cart,
            'customer' => $customer,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(CheckoutRequest $request, CartService $cartService): RedirectResponse
    {
        $cart = $cartService->current();
        $cart->load(['items.product', 'items.productVariant']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('storefront.cart.show')->with('error', 'Your cart is empty.');
        }

        $insufficient = $cart->items->filter(function ($item) {
            $available = $item->productVariant?->availableStock ?? $item->product->currentStock;

            return $item->quantity > $available;
        });

        if ($insufficient->isNotEmpty()) {
            $names = $insufficient->map(fn ($item) => $item->product->name)->implode(', ');

            return redirect()->route('storefront.cart.show')
                ->with('error', "Some items no longer have enough stock: {$names}. Please adjust quantities.");
        }

        $order = DB::transaction(function () use ($request, $cart) {
            $subtotal = (float) $cart->subtotal;
            $vatAmount = (float) $cart->vatAmount;
            $total = (float) $cart->total;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => auth('customer')->id(),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'delivery_method' => $request->validated('delivery_method'),
                'delivery_address' => $request->validated('delivery_address'),
                'warehouse_id' => $request->validated('warehouse_id'),
                'status' => 'pending_confirmation',
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'note' => $request->validated('note'),
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->productVariant?->sku ?? $item->product->sku,
                    'source' => $item->source,
                    'source_label' => $item->source_label,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->lineTotal,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });

        Mail::to($order->email)->send(new OrderConfirmationMail($order));

        // SMS notifications are not integrated yet — stubbed for a future phase.
        Log::info("SMS stub: order confirmation would be sent for {$order->order_number} to {$order->phone}");

        if (setting('notifications.pending_order_enabled', true)) {
            $staff = User::role(setting('notifications.pending_order_roles', ['Admin', 'Manager', 'Warehouse Staff']))->get();
            if ($staff->isNotEmpty()) {
                Notification::send($staff, new NewPendingOrderNotification($order));
            }
        }

        return redirect()->route('storefront.orders.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        $order->load(['items', 'warehouse']);

        return view('storefront.orders.confirmation', ['order' => $order]);
    }
}
