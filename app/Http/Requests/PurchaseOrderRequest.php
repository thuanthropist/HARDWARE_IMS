<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'expected_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'distinct', 'exists:product_variants,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function headerForModel(): array
    {
        return [
            'supplier_id' => $this->integer('supplier_id'),
            'warehouse_id' => $this->integer('warehouse_id'),
            'expected_date' => $this->input('expected_date'),
        ];
    }

    public function itemsForModel(): array
    {
        return collect($this->input('items'))->map(fn (array $item) => [
            'product_variant_id' => (int) $item['product_variant_id'],
            'quantity_ordered' => (int) $item['quantity_ordered'],
            'quantity_received' => 0,
            'unit_cost' => (float) $item['unit_cost'],
            'subtotal' => (int) $item['quantity_ordered'] * (float) $item['unit_cost'],
        ])->all();
    }
}
