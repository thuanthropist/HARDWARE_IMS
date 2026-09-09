<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorFormula extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculator_type_id',
        'output_key',
        'label',
        'formula_expression',
        'unit',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function calculatorType(): BelongsTo
    {
        return $this->belongsTo(CalculatorType::class);
    }
}
