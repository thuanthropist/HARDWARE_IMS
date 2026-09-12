<?php

namespace Mrrh\LicenseClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mrrh\LicenseClient\LicenseManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware `license.feature:{key}` — gates a route/module behind a
 * specific feature flag on the current license (e.g. `license.feature:ai_assistant`).
 * Aborts 403 with a friendly message rather than a generic Laravel error page.
 */
class CheckLicenseFeature
{
    public function __construct(protected LicenseManager $license)
    {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! $this->license->hasFeature($feature)) {
            abort(403, sprintf(
                'This feature is not included in your current license plan. Contact %s to upgrade.',
                config('license-client.support.name', 'your vendor')
            ));
        }

        return $next($request);
    }
}
