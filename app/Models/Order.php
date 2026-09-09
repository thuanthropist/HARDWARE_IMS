<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVatRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    use HasVatRate;

    protected $fillable = [
        'order_number',
        'customer_id',
        'quote_id',
        'name',
        'email',
        'phone',
        'delivery_method',
        'delivery_address',
        'warehouse_id',
        'status',
        'subtotal',
        'vat_amount',
        'total',
        'note',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function scopePendingConfirmation(Builder $query): Builder
    {
        return $query->where('status', 'pending_confirmation');
    }

    public static function generateOrderNumber(): string
    {
        return app(\App\Services\DocumentNumberGenerator::class)->generate(static::class, 'order_number', 'order');
    }
}
