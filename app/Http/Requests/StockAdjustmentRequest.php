<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', Rule::in(['add', 'remove'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', Rule::in(['damage', 'loss', 'count_correction', 'expiry', 'breakage'])],
            'note' => ['required', 'string', 'max:1000'],
        ];
    }

    public function validatedForModel(): array
    {
        return $this->validated();
    }
}
