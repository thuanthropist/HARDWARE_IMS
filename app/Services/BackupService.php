<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Manual database backup via mysqldump — deliberately minimal (no
 * spatie/laravel-backup or similar package installed). Dumps are gzipped
 * and stored on the private "local" disk under backups/, never a public
 * one, since they contain full customer/order data and password hashes.
 */
class BackupService
{
    private const DIRECTORY = 'backups';

    /**
     * @return array{success: bool, filename: ?string, message: string}
     */
    public function create(): array
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if (($dbConfig['driver'] ?? null) !== 'mysql') {
            return [
                'success' => false,
                'filename' => null,
                'message' => 'Manual backup only supports MySQL connections right now — this app is configured for '.($dbConfig['driver'] ?? 'an unknown driver').'.',
            ];
        }

        $disk = Storage::disk('local');
        $disk->makeDirectory(self::DIRECTORY);

        $filename = 'backup-'.now()->format('Y-m-d_His').'.sql.gz';
        $fullPath = $disk->path(self::DIRECTORY.'/'.$filename);

        // Credentials go in a temp mysql option file rather than a
        // --password= flag, so they don't show up in the process list.
        $optionFile = tempnam(sys_get_temp_dir(), 'mysqldump_');
        file_put_contents($optionFile, sprintf(
            "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
            $dbConfig['host'] ?? '127.0.0.1',
            $dbConfig['port'] ?? 3306,
            $dbConfig['username'] ?? '',
            $dbConfig['password'] ?? '',
        ));

        try {
            $process = new Process([
                config('backup.mysqldump_path'),
                "--defaults-extra-file={$optionFile}",
                '--single-transaction',
                '--routines',
                '--skip-comments',
                $dbConfig['database'],
            ]);
            $process->setTimeout(300);

            // On Windows, a subprocess launched without SystemRoot in its
            // environment fails Winsock init (error 10106,
            // WSAEPROVIDERFAILEDINIT) before it can open any TCP socket —
            // harmless on other OSes, so just merge it in defensively.
            if (DIRECTORY_SEPARATOR === '\\') {
                $process->setEnv(['SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows']);
            }

            $gzip = gzopen($fullPath, 'wb9');

            if ($gzip === false) {
                return ['success' => false, 'filename' => null, 'message' => "Couldn't open {$filename} for writing."];
            }

            $process->run(function (string $type, string $buffer) use ($gzip): void {
                if ($type === Process::OUT) {
                    gzwrite($gzip, $buffer);
                }
            });
            gzclose($gzip);

            if (! $process->isSuccessful()) {
                $disk->delete(self::DIRECTORY.'/'.$filename);

                return [
                    'success' => false,
                    'filename' => null,
                    'message' => 'mysqldump failed: '.trim($process->getErrorOutput()) ?: 'unknown error — is MYSQLDUMP_PATH configured correctly in .env?',
                ];
            }

            return ['success' => true, 'filename' => $filename, 'message' => "Backup {$filename} created."];
        } catch (Throwable $e) {
            $disk->delete(self::DIRECTORY.'/'.$filename);

            return ['success' => false, 'filename' => null, 'message' => 'Backup failed: '.$e->getMessage()];
        } finally {
            @unlink($optionFile);
        }
    }

    /**
     * @return Collection<int, array{filename: string, size: int, created_at: Carbon}>
     */
    public function list(): Collection
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::DIRECTORY)) {
            return collect();
        }

        return collect($disk->files(self::DIRECTORY))
            ->filter(fn (string $path): bool => str_ends_with($path, '.sql.gz'))
            ->map(fn (string $path): array => [
                'filename' => basename($path),
                'size' => $disk->size($path),
                // createFromTimestamp() defaults to UTC regardless of
                // date_default_timezone_set(), unlike now() — must be explicit.
                'created_at' => Carbon::createFromTimestamp($disk->lastModified($path), config('app.timezone')),
            ])
            ->sortByDesc('created_at')
            ->values();
    }
}
