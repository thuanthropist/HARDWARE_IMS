<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class NotificationSettingsRequest extends FormRequest
{
    public const TYPES = ['pending_order', 'pending_quote', 'low_stock', 'batch_expiry', 'quote_expiring'];

    public const SMS_DRIVERS = ['none', 'twilio', 'africastalking', 'beem'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleNames = Role::query()->pluck('name')->all();

        $rules = [
            'sms_driver' => ['required', Rule::in(self::SMS_DRIVERS)],
        ];

        foreach (self::TYPES as $type) {
            $rules["{$type}_enabled"] = ['nullable', 'boolean'];
            $rules["{$type}_roles"] = ['required', 'array', 'min:1'];
            $rules["{$type}_roles.*"] = [Rule::in($roleNames)];
        }

        return $rules;
    }
}
