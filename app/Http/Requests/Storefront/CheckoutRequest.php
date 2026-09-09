<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:30'],
            'delivery_method' => ['required', 'string', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:delivery_method,delivery', 'nullable', 'string', 'max:1000'],
            'warehouse_id' => ['required_if:delivery_method,pickup', 'nullable', 'integer', 'exists:warehouses,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
