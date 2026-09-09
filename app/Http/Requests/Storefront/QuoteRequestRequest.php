<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'project_description' => ['nullable', 'string', 'max:2000'],
            'preferred_contact_method' => ['nullable', 'string', 'in:phone,email,whatsapp'],
            'timeline' => ['nullable', 'string', 'max:100'],
            'calculator_submission_id' => ['nullable', 'integer', 'exists:calculator_submissions,id'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
