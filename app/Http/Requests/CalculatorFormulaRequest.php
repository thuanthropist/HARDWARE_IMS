<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CalculatorFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $calculatorTypeId = $this->route('calculatorType')?->id
            ?? $this->route('calculatorFormula')?->calculator_type_id;
        $formulaId = $this->route('calculatorFormula')?->id;

        return [
            'output_key' => [
                'required', 'string', 'max:100',
                Rule::unique('calculator_formulas', 'output_key')
                    ->where('calculator_type_id', $calculatorTypeId)
                    ->ignore($formulaId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'formula_expression' => ['required', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'output_key' => $this->input('output_key') ? Str::snake($this->input('output_key')) : null,
        ]);
    }

    public function validatedForModel(): array
    {
        $data = $this->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
