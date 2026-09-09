<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorOutputProductMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculator_type_id',
        'output_key',
        'product_attribute_filters',
        'selection_strategy',
    ];

    protected function casts(): array
    {
        return [
            'product_attribute_filters' => 'array',
        ];
    }

    public function calculatorType(): BelongsTo
    {
        return $this->belongsTo(CalculatorType::class);
    }
}
