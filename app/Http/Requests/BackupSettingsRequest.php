<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BackupSettingsRequest extends FormRequest
{
    public const FREQUENCIES = ['none', 'daily', 'weekly'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frequency' => ['required', Rule::in(self::FREQUENCIES)],
        ];
    }
}
