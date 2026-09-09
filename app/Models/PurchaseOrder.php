<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'status',
        'expected_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expected_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn (): float => (float) $this->items->sum('subtotal'),
        );
    }

    public function isFullyReceived(): bool
    {
        return $this->items->every(fn (PurchaseOrderItem $item) => $item->quantity_received >= $item->quantity_ordered);
    }

    public function isPartiallyReceived(): bool
    {
        return $this->items->contains(fn (PurchaseOrderItem $item) => $item->quantity_received > 0)
            && ! $this->isFullyReceived();
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public static function generatePoNumber(): string
    {
        return app(\App\Services\DocumentNumberGenerator::class)->generate(static::class, 'po_number', 'po');
    }
}
