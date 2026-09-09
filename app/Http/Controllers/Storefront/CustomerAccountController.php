<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function orders(Request $request): View
    {
        $orders = Order::query()
            ->where('customer_id', $request->user('customer')->id)
            ->latest()
            ->paginate(10);

        return view('storefront.account.orders', ['orders' => $orders]);
    }

    public function orderShow(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user('customer')->id, 403);

        $order->load(['items', 'warehouse']);

        return view('storefront.account.order-show', ['order' => $order]);
    }

    public function orderPdf(Request $request, Order $order): Response
    {
        abort_unless($order->customer_id === $request->user('customer')->id, 403);

        $order->load(['items', 'warehouse']);

        return Pdf::loadView('pdfs.order', ['order' => $order])->stream("{$order->order_number}.pdf");
    }

    public function quotes(Request $request): View
    {
        $quotes = Quote::query()
            ->where('customer_id', $request->user('customer')->id)
            ->latest()
            ->paginate(10);

        return view('storefront.account.quotes', ['quotes' => $quotes]);
    }

    public function quoteShow(Request $request, Quote $quote): View
    {
        abort_unless($quote->customer_id === $request->user('customer')->id, 403);

        $quote->load(['items.product', 'calculatorSubmission.calculatorType']);

        return view('storefront.account.quote-show', ['quote' => $quote]);
    }

    public function quotePdf(Request $request, Quote $quote): Response
    {
        abort_unless($quote->customer_id === $request->user('customer')->id, 403);
        abort_if($quote->items->isEmpty(), 404);

        $quote->load('items.product');

        return Pdf::loadView('pdfs.quote', ['quote' => $quote])->stream("{$quote->quote_number}.pdf");
    }
}
