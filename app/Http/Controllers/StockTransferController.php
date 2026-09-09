<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StockTransferRequest;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function index(Request $request): View
    {
        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse'])
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('stock-transfers.index', ['transfers' => $transfers]);
    }

    public function create(): View
    {
        return view('stock-transfers.create', $this->formData());
    }

    public function store(StockTransferRequest $request): RedirectResponse
    {
        $transfer = DB::transaction(function () use ($request): StockTransfer {
            $transfer = StockTransfer::create([
                ...$request->headerForModel(),
                'transfer_number' => StockTransfer::generateTransferNumber(),
                'status' => 'pending',
                'initiated_by' => auth()->id(),
            ]);

            $transfer->items()->createMany($request->itemsForModel());

            return $transfer;
        });

        return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created.');
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'initiatedBy', 'receivedBy', 'items.productVariant.product']);

        return view('stock-transfers.show', ['transfer' => $stockTransfer]);
    }

    public function dispatch(StockTransfer $stockTransfer): RedirectResponse
    {
        abort_unless($stockTransfer->status === 'pending', 403, 'Only pending transfers can be dispatched.');

        DB::transaction(function () use ($stockTransfer): void {
            $stockTransfer->load('items.productVariant.product');

            foreach ($stockTransfer->items as $item) {
                $stockLevel = StockLevel::query()
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['product_variant_id' => $item->product_variant_id, 'warehouse_id' => $stockTransfer->from_warehouse_id],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );

                abort_if($item->quantity > $stockLevel->quantity, 422, "Insufficient stock to dispatch {$item->quantity} units — only {$stockLevel->quantity} available at the source warehouse.");

                if ($item->productVariant->product->track_batches) {
                    ProductBatch::deductFefo($item->product_variant_id, $stockTransfer->from_warehouse_id, $item->quantity);
                }

                $stockLevel->decrement('quantity', $item->quantity);

                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $stockTransfer->from_warehouse_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'performed_by' => auth()->id(),
                    'note' => "Dispatched on {$stockTransfer->transfer_number} to {$stockTransfer->toWarehouse->name}",
                ]);
            }

            $stockTransfer->update(['status' => 'in_transit']);

            AuditLogService::log('stock_transfer.dispatched', $stockTransfer);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer dispatched — stock deducted from source warehouse.');
    }

    public function receive(StockTransfer $stockTransfer): RedirectResponse
    {
        abort_unless($stockTransfer->status === 'in_transit', 403, 'Only in-transit transfers can be received.');

        DB::transaction(function () use ($stockTransfer): void {
            $stockTransfer->load('items');

            foreach ($stockTransfer->items as $item) {
                $stockLevel = StockLevel::query()
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['product_variant_id' => $item->product_variant_id, 'warehouse_id' => $stockTransfer->to_warehouse_id],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );
                $stockLevel->increment('quantity', $item->quantity);

                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $stockTransfer->to_warehouse_id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $stockTransfer->id,
                    'performed_by' => auth()->id(),
                    'note' => "Received on {$stockTransfer->transfer_number} from {$stockTransfer->fromWarehouse->name}",
                ]);
            }

            $stockTransfer->update(['status' => 'completed', 'received_by' => auth()->id()]);

            AuditLogService::log('stock_transfer.received', $stockTransfer);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer received — stock added to destination warehouse.');
    }

    public function cancel(StockTransfer $stockTransfer): RedirectResponse
    {
        abort_unless(in_array($stockTransfer->status, ['pending', 'in_transit'], true), 403, 'This transfer can no longer be cancelled.');

        DB::transaction(function () use ($stockTransfer): void {
            if ($stockTransfer->status === 'in_transit') {
                $stockTransfer->load('items');

                foreach ($stockTransfer->items as $item) {
                    $stockLevel = StockLevel::query()
                        ->lockForUpdate()
                        ->firstOrCreate(
                            ['product_variant_id' => $item->product_variant_id, 'warehouse_id' => $stockTransfer->from_warehouse_id],
                            ['quantity' => 0, 'reserved_quantity' => 0]
                        );
                    $stockLevel->increment('quantity', $item->quantity);

                    StockMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'warehouse_id' => $stockTransfer->from_warehouse_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'stock_transfer',
                        'reference_id' => $stockTransfer->id,
                        'performed_by' => auth()->id(),
                        'note' => "Transfer {$stockTransfer->transfer_number} cancelled in transit — stock returned to source",
                    ]);
                }
            }

            $stockTransfer->update(['status' => 'cancelled']);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer cancelled.');
    }

    private function formData(): array
    {
        $variants = ProductVariant::query()
            ->with('product:id,name')
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'label' => "{$variant->product->name} ({$variant->sku})",
            ]);

        return [
            'variants' => $variants,
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
        ];
    }
}
