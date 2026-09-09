<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Cached key-value settings store backing the setting() helper. Reads never
 * hit the database after the first call per request cycle — the full table
 * is cached forever and only invalidated by set()/forget() on save.
 */
class SettingsManager
{
    private const CACHE_KEY = 'settings:all';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $this->serialize($value, $type), 'group' => $group, 'type' => $type],
        );

        $this->forget();
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Setting::query()->get()
                ->mapWithKeys(fn (Setting $setting): array => [
                    $setting->key => $this->cast($setting->value, $setting->type),
                ])
                ->all();
        });
    }

    /**
     * @return array<string, mixed> keys with the group prefix stripped, e.g. group('business') returns 'name' not 'business.name'
     */
    public function group(string $group): array
    {
        $prefix = "{$group}.";

        $values = [];

        foreach ($this->all() as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $values[substr($key, strlen($prefix))] = $value;
            }
        }

        return $values;
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function cast(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => $value === '1',
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    private function serialize(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
