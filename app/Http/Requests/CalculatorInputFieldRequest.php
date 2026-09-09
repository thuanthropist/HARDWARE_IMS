<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CalculatorInputFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $calculatorTypeId = $this->route('calculatorType')?->id
            ?? $this->route('calculatorInputField')?->calculator_type_id;
        $fieldId = $this->route('calculatorInputField')?->id;

        return [
            'field_key' => [
                'required', 'string', 'max:100',
                Rule::unique('calculator_input_fields', 'field_key')
                    ->where('calculator_type_id', $calculatorTypeId)
                    ->ignore($fieldId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'input_type' => ['required', Rule::in(['number', 'select', 'radio', 'text', 'repeater'])],
            'unit' => ['nullable', 'string', 'max:50'],
            'options_json' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'field_key' => $this->input('field_key') ? Str::snake($this->input('field_key')) : null,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $raw = $this->input('options_json');

            if ($raw && json_decode($raw, true) === null && trim($raw) !== 'null') {
                $validator->errors()->add('options_json', 'Must be valid JSON.');
            }
        });
    }

    public function validatedForModel(): array
    {
        $data = $this->validated();
        $data['is_required'] = $this->boolean('is_required');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['options'] = $data['options_json'] ? json_decode((string) $data['options_json'], true) : null;
        unset($data['options_json']);

        return $data;
    }
}
