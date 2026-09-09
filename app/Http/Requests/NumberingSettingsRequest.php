<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NumberingSettingsRequest extends FormRequest
{
    public const TYPES = ['po', 'order', 'quote', 'transfer'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        foreach (self::TYPES as $type) {
            $rules["{$type}_pattern"] = ['required', 'string', 'max:100', 'ends_with:{SEQ}'];
            $rules["{$type}_start"] = ['nullable', 'integer', 'min:1', 'max:9999'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.ends_with' => 'The pattern must end with the {SEQ} placeholder.',
        ];
    }
}
