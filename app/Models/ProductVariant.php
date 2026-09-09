<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'barcode',
        'additional_price',
    ];

    protected function casts(): array
    {
        return [
            'additional_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Department-coded SKU, e.g. ELEC-CABCO-0001, used when a variant SKU isn't set manually.
     */
    public static function generateSku(Product $product): string
    {
        $product->loadMissing('department', 'brand');

        $deptCode = $product->department->code ?: Str::upper(Str::substr($product->department->slug, 0, 4));

        $brandCode = $product->brand
            ? Str::upper(Str::limit(preg_replace('/[^A-Za-z]/', '', $product->brand->name), 5, ''))
            : 'GEN';
        $brandCode = $brandCode ?: 'GEN';

        $sequence = static::query()
            ->whereHas('product', fn ($query) => $query->where('department_id', $product->department_id))
            ->count() + 1;

        return sprintf('%s-%s-%04d', $deptCode, $brandCode, $sequence);
    }

    protected function totalStock(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->stockLevels->sum('quantity'),
        );
    }

    protected function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->stockLevels->sum('quantity') - $this->stockLevels->sum('reserved_quantity'),
        );
    }
}
