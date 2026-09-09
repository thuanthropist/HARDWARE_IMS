<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartiallyFulfillOrderRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'items.*.fulfilled_quantity' => ['required', 'integer', 'min:0'],
            'items.*.action' => ['required', 'string', 'in:backorder,drop'],
        ];
    }
}
