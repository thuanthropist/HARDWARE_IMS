<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CalculatorOutputProductMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $calculatorTypeId = $this->route('calculatorType')?->id
            ?? $this->route('calculatorOutputProductMapping')?->calculator_type_id;
        $mappingId = $this->route('calculatorOutputProductMapping')?->id;

        return [
            'output_key' => [
                'required', 'string', 'max:100',
                Rule::unique('calculator_output_product_mappings', 'output_key')
                    ->where('calculator_type_id', $calculatorTypeId)
                    ->ignore($mappingId),
            ],
            'filters_json' => ['required', 'string'],
            'selection_strategy' => ['required', Rule::in(['cheapest_in_stock', 'cheapest', 'highest_stock'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'output_key' => $this->input('output_key') ? Str::snake($this->input('output_key')) : null,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $raw = $this->input('filters_json');

            if ($raw && json_decode($raw, true) === null) {
                $validator->errors()->add('filters_json', 'Must be valid JSON, e.g. {"category":"cables-wires","attributes":{"cable_gauge":"2.5mm²"}}.');
            }
        });
    }

    public function validatedForModel(): array
    {
        $data = $this->validated();
        $data['product_attribute_filters'] = json_decode((string) $data['filters_json'], true) ?? [];
        unset($data['filters_json']);

        return $data;
    }
}
