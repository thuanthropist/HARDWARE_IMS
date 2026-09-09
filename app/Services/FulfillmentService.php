<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

/**
 * Deducts stock for a set of line items against a single warehouse, respecting
 * FEFO for batch-tracked products. Shared between order confirmation (Phase 5
 * approval dashboard) and the POS module so both paths hit stock the same way.
 */
class FulfillmentService
{
    /**
     * @param  array<int, array{product_variant_id: int, quantity: int}>  $lines
     */
    public function deduct(array $lines, int $warehouseId, string $referenceType, int $referenceId, ?int $performedBy = null): void
    {
        $performedBy ??= Auth::id();

        foreach ($lines as $line) {
            if ($line['quantity'] <= 0) {
                continue;
            }

            $this->deductLine((int) $line['product_variant_id'], $warehouseId, (int) $line['quantity'], $referenceType, $referenceId, $performedBy);
        }
    }

    /**
     * True if every line has enough available stock at the warehouse, without
     * making any changes — used to re-validate before a confirm/partial-fulfill
     * transaction actually deducts anything.
     *
     * @param  array<int, array{product_variant_id: int, quantity: int}>  $lines
     * @return array<int, array{product_variant_id: int, requested: int, available: int}> shortfalls, empty if all lines are coverable
     */
    public function findShortfalls(array $lines, int $warehouseId): array
    {
        $shortfalls = [];

        foreach ($lines as $line) {
            if ($line['quantity'] <= 0) {
                continue;
            }

            $available = (int) (StockLevel::query()
                ->where('product_variant_id', $line['product_variant_id'])
                ->where('warehouse_id', $warehouseId)
                ->value('quantity') ?? 0);

            if ($line['quantity'] > $available) {
                $shortfalls[] = [
                    'product_variant_id' => (int) $line['product_variant_id'],
                    'requested' => (int) $line['quantity'],
                    'available' => $available,
                ];
            }
        }

        return $shortfalls;
    }

    private function deductLine(int $productVariantId, int $warehouseId, int $quantity, string $referenceType, int $referenceId, ?int $performedBy): void
    {
        $stockLevel = StockLevel::query()
            ->lockForUpdate()
            ->firstOrCreate(
                ['product_variant_id' => $productVariantId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

        abort_if(
            $quantity > $stockLevel->quantity,
            422,
            "Not enough stock available for this item — only {$stockLevel->quantity} left at this warehouse."
        );

        $variant = ProductVariant::with('product')->findOrFail($productVariantId);

        if ($variant->product->track_batches) {
            ProductBatch::deductFefo($productVariantId, $warehouseId, $quantity);
        }

        $stockLevel->decrement('quantity', $quantity);

        StockMovement::create([
            'product_variant_id' => $productVariantId,
            'warehouse_id' => $warehouseId,
            'type' => 'out',
            'quantity' => -$quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'performed_by' => $performedBy,
        ]);
    }
}
