<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CalculatorTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $calculatorTypeId = $this->route('calculatorType')?->id;

        return [
            'key' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('calculator_types', 'key')->ignore($calculatorTypeId)],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'key' => $this->input('key') ? Str::slug($this->input('key'), '_') : null,
        ]);
    }

    public function validatedForModel(): array
    {
        $data = $this->validated();
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}
