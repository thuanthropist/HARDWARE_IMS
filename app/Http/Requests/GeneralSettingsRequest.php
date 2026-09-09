<?php

declare(strict_types=1);

namespace App\Http\Requests;

use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'timezone' => ['required', 'string', Rule::in(DateTimeZone::listIdentifiers())],
            'date_format' => ['required', 'string', Rule::in(array_keys(self::dateFormats()))],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string> PHP date() format => human-readable example
     */
    public static function dateFormats(): array
    {
        $now = now();

        return [
            'd/m/Y' => $now->format('d/m/Y'),
            'Y-m-d' => $now->format('Y-m-d'),
            'M d, Y' => $now->format('M d, Y'),
            'd M Y' => $now->format('d M Y'),
        ];
    }
}
