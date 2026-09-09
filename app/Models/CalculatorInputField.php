<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculatorInputField extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculator_type_id',
        'field_key',
        'label',
        'input_type',
        'options',
        'unit',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function calculatorType(): BelongsTo
    {
        return $this->belongsTo(CalculatorType::class);
    }

    /**
     * For input_type = repeater, the sub-field schema stored under options.fields.
     */
    public function repeaterFields(): array
    {
        return $this->options['fields'] ?? [];
    }

    /**
     * For input_type = select/radio, the choice list stored under options.choices.
     */
    public function choices(): array
    {
        return $this->options['choices'] ?? [];
    }
}
