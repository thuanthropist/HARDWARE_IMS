<?php

namespace Mrrh\LicenseClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mrrh\LicenseClient\LicenseManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware `license.check`.
 *
 *   not_activated / invalid — block everything, redirect to the "blocked" page
 *   expired                 — block everything except configured exempt routes,
 *                              redirect to the "expired" page
 *   grace                   — pass through (the grace-banner component handles
 *                              the warning, this middleware does nothing extra)
 *   active                  — pass through silently
 *
 * Must run as route/group middleware (after routing), not global middleware —
 * it needs the current route resolved to check names/exemptions.
 */
class CheckLicense
{
    public function __construct(protected LicenseManager $license)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        return match ($this->license->status()) {
            'not_activated', 'invalid' => $this->redirectUnlessOnRoute($request, $next, (string) config('license-client.routes.blocked')),
            'expired' => $this->handleExpired($request, $next),
            default => $next($request),
        };
    }

    protected function handleExpired(Request $request, Closure $next): Response
    {
        $expiredRoute = (string) config('license-client.routes.expired');

        if ($request->routeIs($expiredRoute) || $this->isExempt($request)) {
            return $next($request);
        }

        return redirect()->route($expiredRoute);
    }

    protected function redirectUnlessOnRoute(Request $request, Closure $next, string $routeName): Response
    {
        if ($request->routeIs($routeName)) {
            return $next($request);
        }

        return redirect()->route($routeName);
    }

    protected function isExempt(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, config('license-client.expired_exempt_route_names', []), true)) {
            return true;
        }

        foreach (config('license-client.expired_exempt_patterns', []) as $pattern) {
            if ($request->is(ltrim((string) $pattern, '/'))) {
                return true;
            }
        }

        return false;
    }
}
