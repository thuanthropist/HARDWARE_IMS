<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'distinct', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('from_warehouse_id') === $this->input('to_warehouse_id')) {
                $validator->errors()->add('to_warehouse_id', 'Source and destination warehouses must be different.');
            }
        });
    }

    public function headerForModel(): array
    {
        return [
            'from_warehouse_id' => $this->integer('from_warehouse_id'),
            'to_warehouse_id' => $this->integer('to_warehouse_id'),
        ];
    }

    public function itemsForModel(): array
    {
        return collect($this->input('items'))->map(fn (array $item) => [
            'product_variant_id' => (int) $item['product_variant_id'],
            'quantity' => (int) $item['quantity'],
        ])->all();
    }
}
