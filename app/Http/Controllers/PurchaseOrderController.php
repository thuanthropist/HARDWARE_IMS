<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderReceiveRequest;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'warehouse'])
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('supplier_id'), fn ($query) => $query->where('supplier_id', $request->integer('supplier_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('purchase-orders.create', $this->formData());
    }

    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        $purchaseOrder = DB::transaction(function () use ($request): PurchaseOrder {
            $purchaseOrder = PurchaseOrder::create([
                ...$request->headerForModel(),
                'po_number' => PurchaseOrder::generatePoNumber(),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $purchaseOrder->items()->createMany($request->itemsForModel());

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order created as draft.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.productVariant.product.productAttributes']);

        return view('purchase-orders.show', ['purchaseOrder' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        abort_unless($purchaseOrder->status === 'draft', 403, 'Only draft purchase orders can be edited.');

        $purchaseOrder->load('items');

        return view('purchase-orders.edit', [...$this->formData(), 'purchaseOrder' => $purchaseOrder]);
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless($purchaseOrder->status === 'draft', 403, 'Only draft purchase orders can be edited.');

        DB::transaction(function () use ($request, $purchaseOrder): void {
            $purchaseOrder->update($request->headerForModel());
            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->createMany($request->itemsForModel());
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order updated.');
    }

    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless($purchaseOrder->status === 'draft', 403, 'Only draft purchase orders can be sent.');

        $purchaseOrder->update(['status' => 'sent']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order sent to supplier.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_if(in_array($purchaseOrder->status, ['received', 'cancelled'], true), 403, 'This purchase order can no longer be cancelled.');

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order cancelled.');
    }

    public function receive(PurchaseOrder $purchaseOrder): View
    {
        abort_unless(in_array($purchaseOrder->status, ['sent', 'partially_received'], true), 403, 'This purchase order is not open for receiving.');

        $purchaseOrder->load(['supplier', 'warehouse', 'items.productVariant.product.productAttributes']);

        return view('purchase-orders.receive', ['purchaseOrder' => $purchaseOrder]);
    }

    public function processReceive(PurchaseOrderReceiveRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(in_array($purchaseOrder->status, ['sent', 'partially_received'], true), 403, 'This purchase order is not open for receiving.');

        DB::transaction(function () use ($request, $purchaseOrder): void {
            foreach ($request->receivedLines() as $line) {
                /** @var PurchaseOrderItem $item */
                $item = PurchaseOrderItem::query()
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->lockForUpdate()
                    ->findOrFail($line['purchase_order_item_id']);

                $quantity = min($line['quantity_received_now'], $item->quantity_remaining);

                if ($quantity <= 0) {
                    continue;
                }

                $item->increment('quantity_received', $quantity);

                $stockLevel = StockLevel::query()
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['product_variant_id' => $item->product_variant_id, 'warehouse_id' => $purchaseOrder->warehouse_id],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );
                $stockLevel->increment('quantity', $quantity);

                if ($item->productVariant->product->track_batches) {
                    ProductBatch::create([
                        'product_variant_id' => $item->product_variant_id,
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'batch_number' => $line['batch_number'],
                        'quantity' => $quantity,
                        'expiry_date' => $line['expiry_date'],
                        'received_date' => now()->toDateString(),
                        'purchase_order_item_id' => $item->id,
                    ]);
                }

                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $purchaseOrder->id,
                    'performed_by' => auth()->id(),
                    'note' => "Received against {$purchaseOrder->po_number}",
                ]);
            }

            $purchaseOrder->load('items');
            $purchaseOrder->update([
                'status' => $purchaseOrder->isFullyReceived() ? 'received' : 'partially_received',
            ]);

            AuditLogService::log('purchase_order.received', $purchaseOrder, null, ['status' => $purchaseOrder->status]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Goods received and stock updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless($purchaseOrder->status === 'draft', 403, 'Only draft purchase orders can be deleted.');

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted.');
    }

    private function formData(): array
    {
        $suppliers = Supplier::with('departments')->active()->orderBy('name')->get();

        $variants = ProductVariant::query()
            ->with('product:id,name,department_id,cost_price')
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'label' => "{$variant->product->name} ({$variant->sku})",
                'department_id' => $variant->product->department_id,
                'cost_price' => (float) $variant->product->cost_price,
            ]);

        return [
            'suppliers' => $suppliers,
            'suppliersJson' => $suppliers->map(fn (Supplier $supplier) => [
                'id' => $supplier->id,
                'department_ids' => $supplier->departments->pluck('id'),
            ]),
            'variants' => $variants,
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ];
    }
}
