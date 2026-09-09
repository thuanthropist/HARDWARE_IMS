<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConvertQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Mail\QuoteSentMail;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\FulfillmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'requested';

        $quotes = Quote::query()
            ->with('customer')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quotes.index', [
            'quotes' => $quotes,
            'status' => $status,
            'pendingCount' => Quote::pending()->count(),
        ]);
    }

    public function show(Quote $quote): View
    {
        $quote->load(['customer', 'calculatorSubmission.calculatorType', 'items.product', 'reviewedBy', 'order']);

        return view('quotes.show', [
            'quote' => $quote,
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $products = Product::query()
            ->active()
            ->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'name', 'sku', 'selling_price']);

        return response()->json($products);
    }

    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        abort_if(in_array($quote->status, ['accepted', 'expired'], true), 403, 'This quote can no longer be edited.');

        DB::transaction(function () use ($quote, $request): void {
            $quote->items()->delete();

            $subtotal = 0.0;

            foreach ($request->validated('items') as $index => $item) {
                $lineTotal = round((float) $item['unit_price'] * (int) $item['quantity'], 2);
                $subtotal += $lineTotal;

                $quote->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'sort_order' => $index,
                ]);
            }

            $vatAmount = round($subtotal * setting('tax.vat_rate', 0.18), 2);

            $quote->update([
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total' => round($subtotal + $vatAmount, 2),
                'valid_until' => $request->validated('valid_until'),
                'internal_notes' => $request->validated('internal_notes'),
                'reviewed_by' => auth()->id(),
                'status' => $quote->status === 'requested' ? 'reviewed' : $quote->status,
            ]);

            AuditLogService::log('quote.reviewed', $quote, null, ['total' => $quote->total]);
        });

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote updated.');
    }

    public function send(Quote $quote): RedirectResponse
    {
        abort_unless(in_array($quote->status, ['requested', 'reviewed'], true), 403, 'Only reviewed quotes can be sent.');
        abort_if($quote->items->isEmpty(), 422, 'Add at least one line item before sending.');
        abort_if($quote->valid_until === null, 422, 'Set a validity date before sending.');

        $quote->update(['status' => 'sent', 'sent_at' => now()]);

        AuditLogService::log('quote.sent', $quote);

        Mail::to($quote->email)->send(new QuoteSentMail($quote->fresh(['items.product'])));

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote sent to customer.');
    }

    public function markAccepted(Quote $quote): RedirectResponse
    {
        abort_unless($quote->status === 'sent', 403, 'Only sent quotes can be marked accepted.');

        $quote->update(['status' => 'accepted']);

        AuditLogService::log('quote.accepted', $quote);

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote marked as accepted.');
    }

    public function markRejected(Quote $quote): RedirectResponse
    {
        abort_unless($quote->status === 'sent', 403, 'Only sent quotes can be marked rejected.');

        $quote->update(['status' => 'rejected']);

        AuditLogService::log('quote.rejected', $quote);

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote marked as rejected.');
    }

    public function pdf(Quote $quote): Response
    {
        $quote->load('items.product');

        return Pdf::loadView('pdfs.quote', ['quote' => $quote])->stream("{$quote->quote_number}.pdf");
    }

    public function convertToOrder(ConvertQuoteRequest $request, Quote $quote, FulfillmentService $fulfillment): RedirectResponse
    {
        abort_unless($quote->status === 'accepted', 403, 'Only accepted quotes can be converted to an order.');
        abort_if($quote->order()->exists(), 403, 'This quote has already been converted to an order.');

        $quote->load('items.product.variants');

        $warehouseId = (int) $request->validated('warehouse_id');

        $lines = $quote->items
            ->filter(fn ($item) => $item->product_id !== null)
            ->map(fn ($item) => [
                'product_variant_id' => $item->productVariant?->id ?? $item->product->variants->first()?->id,
                'quantity' => $item->quantity,
            ])
            ->filter(fn (array $line) => $line['product_variant_id'] !== null)
            ->values()
            ->all();

        $shortfalls = $fulfillment->findShortfalls($lines, $warehouseId);
        abort_if($shortfalls !== [], 422, 'Some quoted materials no longer have enough stock at this warehouse.');

        $order = DB::transaction(function () use ($quote, $request, $warehouseId, $fulfillment, $lines): Order {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $quote->customer_id,
                'quote_id' => $quote->id,
                'name' => $quote->name,
                'email' => $quote->email,
                'phone' => $quote->phone,
                'delivery_method' => $request->validated('delivery_method'),
                'delivery_address' => $request->validated('delivery_address'),
                'warehouse_id' => $warehouseId,
                'status' => 'confirmed',
                'subtotal' => $quote->subtotal,
                'vat_amount' => $quote->vat_amount,
                'total' => $quote->total,
                'note' => "Converted from quote {$quote->quote_number}.",
            ]);

            foreach ($quote->items as $item) {
                $variant = $item->productVariant ?? $item->product?->variants->first();

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $item->label,
                    'sku' => $variant?->sku ?? '—',
                    'description' => $item->product_id ? null : $item->description,
                    'source' => 'manual',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                    'fulfilled_quantity' => $item->quantity,
                    'fulfillment_status' => 'fulfilled',
                ]);
            }

            $fulfillment->deduct($lines, $warehouseId, 'order', $order->id);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'confirmed',
                'note' => "Order created from accepted quote {$quote->quote_number} — already priced and agreed, stock deducted.",
                'changed_by' => auth()->id(),
            ]);

            AuditLogService::log('quote.converted_to_order', $quote, null, ['order_id' => $order->id]);

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', "Quote converted to order {$order->order_number}.");
    }
}
