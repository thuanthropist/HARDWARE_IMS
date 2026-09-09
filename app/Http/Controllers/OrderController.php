<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\PartiallyFulfillOrderRequest;
use App\Http\Requests\RejectOrderRequest;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\FulfillmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'pending_confirmation';

        $orders = Order::query()
            ->with('customer')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'status' => $status,
            'pendingCount' => Order::pendingConfirmation()->count(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'customer',
            'warehouse',
            'items.product.department',
            'items.productVariant',
            'statusHistory.changedBy',
        ]);

        $stockByLine = $order->items->mapWithKeys(function (OrderItem $item) use ($order) {
            $available = $order->warehouse_id
                ? (int) (StockLevel::query()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->value('quantity') ?? 0)
                : (int) $item->product->currentStock;

            return [$item->id => $available];
        });

        $groups = $order->items->groupBy(fn (OrderItem $item) => $item->source === 'calculator'
            ? ($item->source_label ?? 'From Planning Tool')
            : 'Manually Added');

        return view('orders.show', [
            'order' => $order,
            'stockByLine' => $stockByLine,
            'groups' => $groups,
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'nextStatus' => $this->nextStatus($order),
        ]);
    }

    public function confirm(ConfirmOrderRequest $request, Order $order, FulfillmentService $fulfillment): RedirectResponse
    {
        abort_unless($order->status === 'pending_confirmation', 403, 'Only pending orders can be confirmed.');

        $warehouseId = $order->warehouse_id ?? (int) $request->validated('warehouse_id');

        $lines = $order->items->map(fn (OrderItem $item) => [
            'product_variant_id' => $item->product_variant_id,
            'quantity' => $item->quantity,
        ])->all();

        $shortfalls = $fulfillment->findShortfalls($lines, $warehouseId);
        abort_if($shortfalls !== [], 422, 'Some items no longer have enough stock to fully confirm this order — use Partially Fulfill instead.');

        DB::transaction(function () use ($order, $warehouseId, $fulfillment, $lines): void {
            $fulfillment->deduct($lines, $warehouseId, 'order', $order->id);

            foreach ($order->items as $item) {
                $item->update(['fulfilled_quantity' => $item->quantity, 'fulfillment_status' => 'fulfilled']);
            }

            $order->update(['warehouse_id' => $warehouseId]);

            $this->transitionStatus($order, 'confirmed', 'Order confirmed by staff — stock deducted.');
            $this->transitionStatus($order, 'processing', 'Moved to processing.');

            AuditLogService::log('order.confirmed', $order, null, ['warehouse_id' => $warehouseId]);
        });

        $confirmed = $order->fresh(['items', 'warehouse']);

        Mail::to($order->email)->send(
            (new OrderStatusUpdateMail(
                $confirmed,
                'Order Confirmed',
                'Great news — your order has been confirmed and is now being processed. Your invoice is attached.'
            ))->attachData(
                Pdf::loadView('pdfs.order', ['order' => $confirmed])->output(),
                "{$confirmed->order_number}.pdf",
                ['mime' => 'application/pdf']
            )
        );

        return redirect()->route('orders.show', $order)->with('success', 'Order confirmed — stock has been deducted.');
    }

    public function partiallyFulfill(PartiallyFulfillOrderRequest $request, Order $order, FulfillmentService $fulfillment): RedirectResponse
    {
        abort_unless($order->status === 'pending_confirmation', 403, 'Only pending orders can be partially fulfilled.');

        $warehouseId = $order->warehouse_id ?? (int) $request->validated('warehouse_id');
        $itemsInput = collect($request->validated('items'))->keyBy('order_item_id');

        $lines = $order->items->map(function (OrderItem $item) use ($itemsInput) {
            $input = $itemsInput->get($item->id);
            $qty = $input ? min((int) $input['fulfilled_quantity'], $item->quantity) : 0;

            return ['product_variant_id' => $item->product_variant_id, 'quantity' => $qty];
        })->filter(fn (array $line) => $line['quantity'] > 0)->values()->all();

        $shortfalls = $fulfillment->findShortfalls($lines, $warehouseId);
        abort_if($shortfalls !== [], 422, 'Some fulfilled quantities exceed available stock at this warehouse.');

        DB::transaction(function () use ($order, $warehouseId, $fulfillment, $lines, $itemsInput): void {
            $fulfillment->deduct($lines, $warehouseId, 'order', $order->id);

            foreach ($order->items as $item) {
                $input = $itemsInput->get($item->id);
                $fulfilledQty = $input ? min((int) $input['fulfilled_quantity'], $item->quantity) : 0;
                $remainder = $item->quantity - $fulfilledQty;
                $action = $input['action'] ?? 'backorder';

                $status = match (true) {
                    $fulfilledQty === $item->quantity => 'fulfilled',
                    $remainder > 0 && $action === 'drop' => 'dropped',
                    $remainder > 0 => 'backordered',
                    default => 'pending',
                };

                $item->update(['fulfilled_quantity' => $fulfilledQty, 'fulfillment_status' => $status]);
            }

            $order->update(['warehouse_id' => $warehouseId]);

            $this->transitionStatus($order, 'partially_fulfilled', 'Order partially fulfilled by staff.');

            AuditLogService::log('order.partially_fulfilled', $order, null, ['warehouse_id' => $warehouseId]);
        });

        Mail::to($order->email)->send(new OrderStatusUpdateMail(
            $order->fresh(),
            'Order Partially Fulfilled',
            'Some items in your order are ready; others are on backorder or unavailable. See the breakdown below.'
        ));

        return redirect()->route('orders.show', $order)->with('success', 'Order partially fulfilled.');
    }

    public function reject(RejectOrderRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === 'pending_confirmation', 403, 'Only pending orders can be rejected.');

        DB::transaction(function () use ($order, $request): void {
            $order->update(['rejection_reason' => $request->validated('reason')]);

            $this->transitionStatus($order, 'rejected', $request->validated('reason'));

            AuditLogService::log('order.rejected', $order, null, ['reason' => $request->validated('reason')]);
        });

        Mail::to($order->email)->send(new OrderStatusUpdateMail(
            $order->fresh(),
            'Order Rejected',
            "We're sorry, but we're unable to fulfill this order: {$request->validated('reason')}"
        ));

        return redirect()->route('orders.index')->with('success', 'Order rejected. Customer has been notified.');
    }

    public function progress(Order $order): RedirectResponse
    {
        $next = $this->nextStatus($order);

        abort_if($next === null, 403, 'This order has no further status to progress to.');

        DB::transaction(function () use ($order, $next): void {
            $this->transitionStatus($order, $next, 'Marked as '.str_replace('_', ' ', $next).' by staff.');

            AuditLogService::log('order.progressed', $order, null, ['to_status' => $next]);
        });

        if ($next === 'completed') {
            Mail::to($order->email)->send(new OrderStatusUpdateMail(
                $order->fresh(['items']),
                'Order Completed',
                'Your order is complete. Thank you for shopping with Hardware IMS!'
            ));
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order status updated.');
    }

    public function pdf(Order $order): Response
    {
        $order->load(['items', 'warehouse']);

        return Pdf::loadView('pdfs.order', ['order' => $order])->stream("{$order->order_number}.pdf");
    }

    private function nextStatus(Order $order): ?string
    {
        return match ($order->status) {
            'confirmed', 'processing', 'partially_fulfilled' => $order->delivery_method === 'pickup' ? 'ready_for_pickup' : 'out_for_delivery',
            'ready_for_pickup', 'out_for_delivery' => 'completed',
            default => null,
        };
    }

    private function transitionStatus(Order $order, string $to, ?string $note = null): void
    {
        $from = $order->status;

        $order->update(['status' => $to]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'changed_by' => auth()->id(),
        ]);
    }
}
