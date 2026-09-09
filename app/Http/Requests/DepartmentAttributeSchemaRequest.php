<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DepartmentAttributeSchemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id ?? $this->route('attributeSchema')?->department_id;
        $schemaId = $this->route('attributeSchema')?->id;

        return [
            'label' => ['required', 'string', 'max:255'],
            'attribute_key' => [
                'nullable', 'string', 'max:100',
                Rule::unique('department_attribute_schemas', 'attribute_key')
                    ->where('department_id', $departmentId)
                    ->ignore($schemaId),
            ],
            'input_type' => ['required', 'string', Rule::in(['text', 'number', 'select', 'textarea'])],
            'unit' => ['nullable', 'string', 'max:50'],
            'options' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'attribute_key' => $this->input('attribute_key') ?: Str::snake((string) $this->input('label')),
        ]);
    }

    public function validatedForModel(): array
    {
        $data = $this->validated();
        $data['is_required'] = $this->boolean('is_required');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['options'] = $data['options']
            ? array_values(array_filter(array_map('trim', explode(',', $data['options']))))
            : null;

        return $data;
    }
}
