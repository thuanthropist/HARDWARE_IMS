<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\OrderTrackingRequest;
use App\Models\Order;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(): View
    {
        return view('storefront.orders.track', ['order' => null, 'notFound' => false]);
    }

    public function find(OrderTrackingRequest $request): View
    {
        $order = Order::query()
            ->where('order_number', $request->validated('order_number'))
            ->where('email', $request->validated('email'))
            ->with(['items', 'warehouse'])
            ->first();

        return view('storefront.orders.track', [
            'order' => $order,
            'notFound' => $order === null,
        ]);
    }
}
