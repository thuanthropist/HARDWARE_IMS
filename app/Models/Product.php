<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'image_path',
        'department_id',
        'category_id',
        'brand_id',
        'unit_of_measure',
        'cost_price',
        'selling_price',
        'reorder_point',
        'track_batches',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'reorder_point' => 'integer',
            'track_batches' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Flat attribute_key => attribute_value map, for use by Phase 3 calculators.
     */
    public function attributesArray(): array
    {
        return $this->productAttributes->pluck('attribute_value', 'attribute_key')->toArray();
    }

    protected function currentStock(): Attribute
    {
        return Attribute::make(
            get: fn (): int => array_key_exists('computed_stock', $this->attributes)
                ? (int) $this->attributes['computed_stock']
                : (int) StockLevel::query()
                    ->whereIn('product_variant_id', $this->variants()->pluck('id'))
                    ->sum('quantity'),
        );
    }

    /**
     * in_stock / low_stock / out_of_stock, derived from currentStock vs reorder_point.
     * Never exposes the raw quantity to callers (e.g. the storefront).
     */
    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $stock = $this->currentStock;

                return match (true) {
                    $stock <= 0 => 'out_of_stock',
                    $stock <= $this->reorder_point => 'low_stock',
                    default => 'in_stock',
                };
            },
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw(
            '(select coalesce(sum(sl.quantity), 0)
                from product_variants pv
                inner join stock_levels sl on sl.product_variant_id = pv.id
                where pv.product_id = products.id) <= reorder_point'
        );
    }

    /**
     * Adds a computed_stock column via subquery so listing pages can read
     * currentStock/stockStatus without an N+1 query per product.
     */
    public function scopeWithComputedStock(Builder $query): Builder
    {
        return $query->addSelect([
            'computed_stock' => StockLevel::query()
                ->selectRaw('coalesce(sum(stock_levels.quantity), 0)')
                ->join('product_variants', 'product_variants.id', '=', 'stock_levels.product_variant_id')
                ->whereColumn('product_variants.product_id', 'products.id'),
        ]);
    }
}
