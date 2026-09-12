<?php

namespace Mrrh\LicenseClient;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mrrh\LicenseClient\Services\TokenStore;
use Mrrh\LicenseClient\Services\TokenVerifier;

/**
 * Resolves the current license status entirely from the locally stored,
 * signature-verified token — no network call on the request path. This is
 * what the CheckLicense middleware, the License facade, and the grace-banner
 * component all read from.
 *
 * Statuses:
 *   not_activated — no token has ever been stored on this install
 *   invalid       — a token exists but fails signature verification (corrupt,
 *                   tampered, or signed by a different product/key)
 *   active        — signature valid, within expires_at (or no expiry at all)
 *   grace         — signature valid, past expires_at but within grace_period_days
 *   expired       — signature valid, past expires_at + grace_period_days
 */
class LicenseManager
{
    public function __construct(
        protected TokenStore $tokenStore,
        protected TokenVerifier $verifier,
    ) {
    }

    public function status(): string
    {
        return $this->resolve()['status'];
    }

    public function claims(): ?array
    {
        return $this->resolve()['claims'];
    }

    public function isValid(): bool
    {
        return in_array($this->status(), ['active', 'grace'], true);
    }

    public function isInGracePeriod(): bool
    {
        return $this->status() === 'grace';
    }

    public function hasFeature(string $key): bool
    {
        $features = $this->claims()['features_enabled'] ?? [];

        return is_array($features) && in_array($key, $features, true);
    }

    public function maxUsers(): ?int
    {
        $value = $this->claims()['max_users'] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function expiresAt(): ?Carbon
    {
        $raw = $this->claims()['expires_at'] ?? null;

        return $raw ? Carbon::parse($raw) : null;
    }

    /**
     * Positive if expiry is in the future, negative if in the past, null if
     * the license never expires.
     */
    public function daysUntilExpiry(): ?int
    {
        $expiresAt = $this->expiresAt();

        if ($expiresAt === null) {
            return null;
        }

        return intdiv($expiresAt->getTimestamp() - now()->getTimestamp(), 86400);
    }

    /**
     * Drop the cached verification result. Call this after license:activate
     * or license:heartbeat writes a new (or invalidated) token, so the next
     * request re-evaluates status immediately instead of waiting out the TTL.
     */
    public function forgetCache(): void
    {
        Cache::forget($this->cacheKey());
    }

    /**
     * @return array{status: string, claims: ?array}
     */
    protected function resolve(): array
    {
        $ttl = max(1, (int) config('license-client.cache_ttl_minutes', 60));

        return Cache::remember($this->cacheKey(), now()->addMinutes($ttl), function () {
            if (! $this->tokenStore->exists()) {
                return ['status' => 'not_activated', 'claims' => null];
            }

            $token = $this->tokenStore->read();
            $claims = $token !== null ? $this->verifier->verify($token) : null;

            if ($claims === null) {
                return ['status' => 'invalid', 'claims' => null];
            }

            return ['status' => $this->timeStatus($claims), 'claims' => $claims];
        });
    }

    protected function timeStatus(array $claims): string
    {
        $expiresRaw = $claims['expires_at'] ?? null;

        if ($expiresRaw === null || $expiresRaw === '') {
            return 'active';
        }

        $expiresAt = Carbon::parse($expiresRaw);
        $graceDays = max(0, (int) config('license-client.grace_period_days', 5));
        $now = Carbon::now();

        if ($now->lessThanOrEqualTo($expiresAt)) {
            return 'active';
        }

        if ($now->lessThanOrEqualTo($expiresAt->copy()->addDays($graceDays))) {
            return 'grace';
        }

        return 'expired';
    }

    protected function cacheKey(): string
    {
        return 'license-client:verified:'.config('license-client.product_code', 'default');
    }
}
