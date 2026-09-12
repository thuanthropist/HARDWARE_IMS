<?php

namespace Mrrh\LicenseClient\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Reads/writes the locally stored signed license token, encrypted at rest with
 * the host app's APP_KEY. Copying the token file to another server is useless
 * without also having that server's APP_KEY.
 */
class TokenStore
{
    public function __construct(protected string $path)
    {
    }

    public function exists(): bool
    {
        return File::exists($this->path);
    }

    /**
     * Returns the raw signed token string, or null if there is no token file,
     * or it can't be decrypted (corrupted, or written under a different APP_KEY).
     */
    public function read(): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        try {
            return Crypt::decryptString(File::get($this->path));
        } catch (DecryptException $e) {
            Log::warning('license-client: could not decrypt the stored license token.', [
                'path' => $this->path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function write(string $token): void
    {
        File::ensureDirectoryExists(dirname($this->path));
        File::put($this->path, Crypt::encryptString($token));

        // Best-effort on POSIX hosts; silently ignored on Windows.
        @chmod($this->path, 0600);
    }

    public function delete(): void
    {
        if ($this->exists()) {
            File::delete($this->path);
        }
    }
}
