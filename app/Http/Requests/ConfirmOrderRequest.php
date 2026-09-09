<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $order = $this->route('order');

        return [
            'warehouse_id' => [
                Rule::requiredIf($order->warehouse_id === null),
                'nullable', 'integer', 'exists:warehouses,id',
            ],
        ];
    }
}
