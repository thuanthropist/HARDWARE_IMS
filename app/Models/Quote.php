<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVatRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    use HasFactory;
    use HasVatRate;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'name',
        'email',
        'phone',
        'status',
        'source',
        'calculator_submission_id',
        'project_description',
        'preferred_contact_method',
        'timeline',
        'subtotal',
        'vat_amount',
        'total',
        'valid_until',
        'sent_at',
        'expiry_reminder_sent_at',
        'reviewed_by',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'expiry_reminder_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function calculatorSubmission(): BelongsTo
    {
        return $this->belongsTo(CalculatorSubmission::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['requested', 'reviewed']);
    }

    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->valid_until !== null && $this->valid_until->isPast(),
        );
    }

    public static function generateQuoteNumber(): string
    {
        return app(\App\Services\DocumentNumberGenerator::class)->generate(static::class, 'quote_number', 'quote');
    }
}
