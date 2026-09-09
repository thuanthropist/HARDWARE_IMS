<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn (): float => (float) $this->items->sum(fn (CartItem $item) => $item->unit_price * $item->quantity),
        );
    }

    protected function vatAmount(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round($this->subtotal * setting('tax.vat_rate', 0.18), 2),
        );
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round($this->subtotal + $this->vatAmount, 2),
        );
    }
}
