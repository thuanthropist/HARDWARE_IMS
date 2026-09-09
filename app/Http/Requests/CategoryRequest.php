<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
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
            $categoryId = $this->route('category')?->id;
            $departmentId = $this->input('department_id');
            $slug = $this->input('slug');

            if ($departmentId && $slug && Category::where('department_id', $departmentId)
                ->where('slug', $slug)
                ->when($categoryId, fn ($query) => $query->where('id', '!=', $categoryId))
                ->exists()) {
                $validator->errors()->add('slug', 'This slug is already used in the selected department.');
            }

            $parentId = $this->input('parent_id');

            if ($parentId && $departmentId && ! Category::where('id', $parentId)->where('department_id', $departmentId)->exists()) {
                $validator->errors()->add('parent_id', 'The parent category must belong to the same department.');
            }

            if ($parentId && $categoryId && (int) $parentId === (int) $categoryId) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');
            }
        });
    }

    public function validatedForModel(): array
    {
        return $this->validated();
    }
}
