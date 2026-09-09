<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PurchaseOrderReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.purchase_order_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity_received_now' => ['nullable', 'integer', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasAny = false;

            foreach ($this->input('items', []) as $index => $row) {
                $quantity = (int) ($row['quantity_received_now'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $hasAny = true;

                $item = PurchaseOrderItem::with('productVariant.product')->find($row['purchase_order_item_id'] ?? null);

                if (! $item) {
                    continue;
                }

                if ($quantity > $item->quantity_remaining) {
                    $validator->errors()->add(
                        "items.{$index}.quantity_received_now",
                        "Cannot receive more than the {$item->quantity_remaining} units still outstanding."
                    );
                }

                if ($item->productVariant->product->track_batches) {
                    if (empty($row['batch_number'] ?? null)) {
                        $validator->errors()->add("items.{$index}.batch_number", 'Batch number is required for this product.');
                    }

                    if (empty($row['expiry_date'] ?? null)) {
                        $validator->errors()->add("items.{$index}.expiry_date", 'Expiry date is required for this product.');
                    }
                }
            }

            if (! $hasAny) {
                $validator->errors()->add('items', 'Enter a received quantity for at least one line item.');
            }
        });
    }

    public function receivedLines(): array
    {
        return collect($this->input('items'))
            ->map(fn (array $row) => [
                'purchase_order_item_id' => (int) $row['purchase_order_item_id'],
                'quantity_received_now' => (int) ($row['quantity_received_now'] ?? 0),
                'batch_number' => $row['batch_number'] ?? null,
                'expiry_date' => $row['expiry_date'] ?? null,
            ])
            ->filter(fn (array $row) => $row['quantity_received_now'] > 0)
            ->values()
            ->all();
    }
}
