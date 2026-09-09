<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\DepartmentAttributeSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'department_id' => ['required', 'exists:departments,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_point' => ['nullable', 'integer', 'min:0'],
            'track_batches' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') ?: Str::slug((string) $this->input('name')),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $departmentId = $this->input('department_id');
            $categoryId = $this->input('category_id');

            if ($departmentId && $categoryId && ! Category::where('id', $categoryId)->where('department_id', $departmentId)->exists()) {
                $validator->errors()->add('category_id', 'The selected category does not belong to the chosen department.');
            }

            if (! $departmentId) {
                return;
            }

            $schemas = DepartmentAttributeSchema::where('department_id', $departmentId)->get();
            $attributes = $this->input('attributes', []);

            foreach ($schemas as $schema) {
                $value = $attributes[$schema->attribute_key] ?? null;

                if ($schema->is_required && ($value === null || $value === '')) {
                    $validator->errors()->add(
                        "attributes.{$schema->attribute_key}",
                        "{$schema->label} is required for this department."
                    );
                }

                if ($schema->input_type === 'select' && $value !== null && $value !== '' && ! in_array($value, $schema->options ?? [], true)) {
                    $validator->errors()->add(
                        "attributes.{$schema->attribute_key}",
                        "{$schema->label} must be one of the allowed options."
                    );
                }
            }
        });
    }

    public function validatedForModel(): array
    {
        $data = $this->validated();
        $data['is_active'] = $this->boolean('is_active');
        $data['track_batches'] = $this->boolean('track_batches');
        $data['reorder_point'] = $data['reorder_point'] ?? 0;
        unset($data['attributes'], $data['image']);

        return $data;
    }

    public function attributesForModel(): array
    {
        $departmentId = $this->input('department_id');
        $schemas = DepartmentAttributeSchema::where('department_id', $departmentId)->get();
        $submitted = $this->input('attributes', []);

        $attributes = [];

        foreach ($schemas as $schema) {
            $value = $submitted[$schema->attribute_key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $attributes[] = [
                'attribute_key' => $schema->attribute_key,
                'attribute_value' => $value,
                'attribute_unit' => $schema->unit,
            ];
        }

        return $attributes;
    }
}
