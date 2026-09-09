<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $adjustments = StockAdjustment::query()
            ->with(['productVariant.product', 'warehouse', 'requestedBy', 'approvedBy'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock-adjustments.index', ['adjustments' => $adjustments]);
    }

    public function create(): View
    {
        return view('stock-adjustments.create', $this->formData());
    }

    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        $adjustment = DB::transaction(function () use ($request): StockAdjustment {
            $adjustment = StockAdjustment::create([
                ...$request->validatedForModel(),
                'requested_by' => auth()->id(),
                'status' => 'pending',
            ]);

            if (! $adjustment->exceedsThreshold()) {
                $this->applyAdjustment($adjustment, auth()->id());
            }

            return $adjustment;
        });

        $message = $adjustment->status === 'approved'
            ? 'Stock adjustment applied.'
            : 'Stock adjustment submitted for approval (exceeds auto-approval threshold).';

        return redirect()->route('stock-adjustments.show', $adjustment)->with('success', $message);
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load(['productVariant.product.productAttributes', 'warehouse', 'requestedBy', 'approvedBy']);

        return view('stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function approve(StockAdjustment $stockAdjustment): RedirectResponse
    {
        abort_unless($stockAdjustment->status === 'pending', 403, 'Only pending adjustments can be approved.');

        DB::transaction(function () use ($stockAdjustment): void {
            $this->applyAdjustment($stockAdjustment, auth()->id());
        });

        return redirect()->route('stock-adjustments.show', $stockAdjustment)->with('success', 'Adjustment approved and applied to stock.');
    }

    public function reject(StockAdjustment $stockAdjustment): RedirectResponse
    {
        abort_unless($stockAdjustment->status === 'pending', 403, 'Only pending adjustments can be rejected.');

        $stockAdjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLogService::log('stock_adjustment.rejected', $stockAdjustment);

        return redirect()->route('stock-adjustments.show', $stockAdjustment)->with('success', 'Adjustment rejected. No stock changes were made.');
    }

    private function applyAdjustment(StockAdjustment $adjustment, int $approverId): void
    {
        $stockLevel = StockLevel::query()
            ->lockForUpdate()
            ->firstOrCreate(
                ['product_variant_id' => $adjustment->product_variant_id, 'warehouse_id' => $adjustment->warehouse_id],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

        if ($adjustment->type === 'remove' && $adjustment->quantity > $stockLevel->quantity) {
            abort(422, "Cannot remove {$adjustment->quantity} units — only {$stockLevel->quantity} in stock at this warehouse.");
        }

        if ($adjustment->type === 'remove' && $adjustment->productVariant->product->track_batches) {
            ProductBatch::deductFefo($adjustment->product_variant_id, $adjustment->warehouse_id, $adjustment->quantity);
        }

        $stockLevel->increment('quantity', $adjustment->type === 'add' ? $adjustment->quantity : -$adjustment->quantity);

        StockMovement::create([
            'product_variant_id' => $adjustment->product_variant_id,
            'warehouse_id' => $adjustment->warehouse_id,
            'type' => 'adjustment',
            'quantity' => $adjustment->type === 'add' ? $adjustment->quantity : -$adjustment->quantity,
            'reference_type' => 'stock_adjustment',
            'reference_id' => $adjustment->id,
            'performed_by' => $approverId,
            'note' => "{$adjustment->reason}: {$adjustment->note}",
        ]);

        $adjustment->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        AuditLogService::log('stock_adjustment.approved', $adjustment, null, [
            'type' => $adjustment->type,
            'quantity' => $adjustment->quantity,
        ]);
    }

    private function formData(): array
    {
        $variants = ProductVariant::query()
            ->with('product:id,name,department_id')
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'id' => $variant->id,
                'label' => "{$variant->product->name} ({$variant->sku})",
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
            ]);

        return [
            'variants' => $variants,
            'warehouses' => Warehouse::active()->orderBy('name')->get(),
            'thresholdQuantity' => config('inventory.stock_adjustment_threshold_quantity'),
            'thresholdValue' => config('inventory.stock_adjustment_threshold_value'),
        ];
    }
}
