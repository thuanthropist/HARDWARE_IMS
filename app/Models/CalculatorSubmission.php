<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculator_type_id',
        'customer_id',
        'input_data',
        'computed_output',
        'estimated_total',
        'converted_to_cart',
        'converted_to_quote_id',
    ];

    protected function casts(): array
    {
        return [
            'input_data' => 'array',
            'computed_output' => 'array',
            'estimated_total' => 'decimal:2',
            'converted_to_cart' => 'boolean',
        ];
    }

    public function calculatorType(): BelongsTo
    {
        return $this->belongsTo(CalculatorType::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
