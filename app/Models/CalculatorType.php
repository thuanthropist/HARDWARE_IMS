<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalculatorType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'department_id',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function inputFields(): HasMany
    {
        return $this->hasMany(CalculatorInputField::class)->orderBy('sort_order');
    }

    public function formulas(): HasMany
    {
        return $this->hasMany(CalculatorFormula::class)->orderBy('sort_order');
    }

    public function outputProductMappings(): HasMany
    {
        return $this->hasMany(CalculatorOutputProductMapping::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CalculatorSubmission::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Blank/default sample input_data shaped for this calculator's input fields —
     * used to seed the admin formula tester and the preview form.
     */
    public function defaultInputData(): array
    {
        $defaults = [];

        foreach ($this->inputFields as $field) {
            $defaults[$field->field_key] = match ($field->input_type) {
                'repeater' => [],
                'select', 'radio' => array_key_first($field->choices()) ?? '',
                'number' => 0,
                default => '',
            };
        }

        return $defaults;
    }
}
