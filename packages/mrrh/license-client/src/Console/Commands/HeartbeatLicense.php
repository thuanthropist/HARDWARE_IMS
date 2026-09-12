<?php

namespace Mrrh\LicenseClient\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Mrrh\LicenseClient\Console\Commands\Concerns\ResolvesInstallationDetails;
use Mrrh\LicenseClient\LicenseManager;
use Mrrh\LicenseClient\Services\ApiClient;
use Mrrh\LicenseClient\Services\ServerFingerprint;
use Mrrh\LicenseClient\Services\TokenStore;
use Mrrh\LicenseClient\Services\TokenVerifier;
use Throwable;

/**
 * Core offline-first behaviour lives here: any failure to reach or get a sane
 * response from the License Server is logged and swallowed — this command
 * never throws, never prints to the user, and never touches the locally
 * stored token on failure. A site with poor connectivity keeps running on its
 * last verified token until that token's own expires_at + grace period runs
 * out, regardless of how many heartbeats fail in between.
 */
class HeartbeatLicense extends Command
{
    use ResolvesInstallationDetails;

    protected $signature = 'license:heartbeat';

    protected $description = 'Check in with the License Server and refresh the local token if needed. Never fails loudly — offline installs keep running on their last valid token.';

    public function handle(
        ApiClient $api,
        TokenVerifier $verifier,
        TokenStore $store,
        ServerFingerprint $fingerprint,
        LicenseManager $manager,
    ): int {
        if (! $store->exists()) {
            Log::info('license-client: heartbeat skipped, this install has never been activated.');

            return self::SUCCESS;
        }

        $claims = $manager->claims();
        $licenseKey = $claims['license_key'] ?? null;

        if (! is_string($licenseKey) || $licenseKey === '') {
            Log::warning('license-client: heartbeat skipped, the stored token is unreadable or invalid.');

            return self::SUCCESS;
        }

        $domain = is_string($claims['domain'] ?? null) ? $claims['domain'] : $this->currentDomain();
        $serverFingerprint = $fingerprint->generate();
        $appVersion = (string) config('license-client.app_version', '1.0.0');

        try {
            $result = $api->heartbeat($licenseKey, $domain, $serverFingerprint, $appVersion);
        } catch (Throwable $e) {
            Log::warning('license-client: heartbeat request threw an exception; keeping the last local token.', [
                'error' => $e->getMessage(),
            ]);

            return self::SUCCESS;
        }

        if (! $result->ok) {
            Log::warning('license-client: heartbeat was rejected by the License Server; keeping the last local token.', [
                'status' => $result->status,
                'message' => $result->message(),
            ]);

            return self::SUCCESS;
        }

        $status = $result->body['status'] ?? null;

        // The server is authoritative here: if it explicitly reports the
        // license as revoked/suspended, don't wait for the locally cached
        // token's own expiry — invalidate it now. There's no legitimate new
        // token to store for a revoked license, so the honest move is to
        // drop trust in the old one rather than keep serving it.
        if (in_array($status, ['revoked', 'suspended'], true)) {
            $store->delete();
            $manager->forgetCache();

            Log::warning("license-client: heartbeat reported license status \"{$status}\"; local token invalidated.");

            return self::SUCCESS;
        }

        $token = $result->body['signed_token'] ?? null;

        if (! is_string($token) || $token === '') {
            // Still active, nothing changed — nothing to do.
            return self::SUCCESS;
        }

        $newClaims = $verifier->verify($token);

        if ($newClaims === null) {
            Log::warning('license-client: heartbeat returned a token that failed signature verification; ignoring it and keeping the last local token.');

            return self::SUCCESS;
        }

        if (! $this->isFresh($newClaims)) {
            Log::warning('license-client: heartbeat returned a token that failed its freshness check; ignoring it and keeping the last local token.');

            return self::SUCCESS;
        }

        $store->write($token);
        $manager->forgetCache();

        Log::info('license-client: heartbeat refreshed the local license token.');

        return self::SUCCESS;
    }
}
