<?php

namespace Mrrh\LicenseClient\Console\Commands\Concerns;

trait ResolvesInstallationDetails
{
    protected function currentDomain(): string
    {
        $url = (string) config('app.url');
        $host = $url !== '' ? parse_url($url, PHP_URL_HOST) : null;

        return $host ?: (gethostname() ?: 'localhost');
    }

    /**
     * Guards against a captured/replayed server response being fed back to
     * this app later — does not apply to ordinary offline validation of an
     * already-stored token, only to tokens freshly received from the server.
     */
    protected function isFresh(array $claims): bool
    {
        $maxAgeSeconds = max(1, (int) config('license-client.response_max_age_minutes', 5)) * 60;

        return isset($claims['iat']) && (time() - (int) $claims['iat']) <= $maxAgeSeconds;
    }
}
