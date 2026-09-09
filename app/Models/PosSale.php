<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVatRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSale extends Model
{
    use HasFactory;
    use HasVatRate;

    protected $fillable = [
        'sale_number',
        'cashier_id',
        'warehouse_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount_amount',
        'vat_amount',
        'total',
        'payment_method',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['cashier_id'] ?? null, fn (Builder $q, $id) => $q->where('cashier_id', $id))
            ->when($filters['payment_method'] ?? null, fn (Builder $q, $method) => $q->where('payment_method', $method))
            ->when($filters['department_id'] ?? null, fn (Builder $q, $departmentId) => $q->whereHas(
                'items.product',
                fn (Builder $pq) => $pq->where('department_id', $departmentId)
            ));
    }

    public static function generateSaleNumber(): string
    {
        $year = now()->year;

        $lastNumber = static::query()
            ->where('sale_number', 'like', "POS-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('sale_number')
            ->value('sale_number');

        $nextSequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return sprintf('POS-%d-%04d', $year, $nextSequence);
    }
}
