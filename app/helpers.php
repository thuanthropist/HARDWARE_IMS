<?php

declare(strict_types=1);

use App\Services\SettingsManager;

if (! function_exists('setting')) {
    /**
     * Read a cached system setting, e.g. setting('business.name'). Returns
     * the SettingsManager itself when called with no key, mirroring the
     * config()/session() helper convention.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $manager = app(SettingsManager::class);

        return $key === null ? $manager : $manager->get($key, $default);
    }
}
