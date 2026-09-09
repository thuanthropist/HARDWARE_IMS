<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * Generates the next document number for PO/Order/Quote/Transfer records
 * from a settings-configurable pattern (e.g. "PO-{YEAR}-{SEQ}"). {SEQ} must
 * be the last placeholder in the pattern — the sequence is always a 4-digit
 * zero-padded number appended at the very end, which is what lets us derive
 * the next sequence by string-matching the prefix and reading the tail of
 * the highest existing number, instead of a separate counter column.
 */
class DocumentNumberGenerator
{
    /**
     * @var array<string, string>
     */
    private const DEFAULT_PATTERNS = [
        'po' => 'PO-{YEAR}-{SEQ}',
        'order' => 'SO-{YEAR}-{SEQ}',
        'quote' => 'QT-{YEAR}-{SEQ}',
        'transfer' => 'TRF-{YEAR}-{SEQ}',
    ];

    public static function defaultPattern(string $type): string
    {
        return self::DEFAULT_PATTERNS[$type] ?? throw new \InvalidArgumentException("Unknown numbering type [{$type}].");
    }

    /**
     * Generate the next number and lock the matching rows for the duration
     * of the caller's transaction, so two concurrent creates can't derive
     * the same sequence. Must be called inside DB::transaction().
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    public function generate(string $modelClass, string $column, string $type): string
    {
        return $this->resolveNext($modelClass, $column, $type, lock: true);
    }

    /**
     * Read-only preview of the next number, for display in the settings UI.
     * Not lock-safe — never use this to actually assign a number.
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    public function preview(string $modelClass, string $column, string $type): string
    {
        return $this->resolveNext($modelClass, $column, $type, lock: false);
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    private function resolveNext(string $modelClass, string $column, string $type, bool $lock): string
    {
        $prefixText = $this->resolvePrefix($type);

        /** @var Builder $query */
        $query = $modelClass::query()->where($column, 'like', "{$prefixText}%")->orderByDesc($column);

        if ($lock) {
            $query->lockForUpdate();
        }

        $last = $query->value($column);

        $nextSequence = $last
            ? ((int) substr($last, strlen($prefixText))) + 1
            : (int) setting("numbering.{$type}_start", 1);

        return $prefixText.sprintf('%04d', $nextSequence);
    }

    private function resolvePrefix(string $type): string
    {
        $pattern = setting("numbering.{$type}_pattern", self::defaultPattern($type));

        $seqPos = strrpos($pattern, '{SEQ}');
        $prefixPattern = $seqPos !== false ? substr($pattern, 0, $seqPos) : $pattern;

        return str_replace('{YEAR}', (string) now()->year, $prefixPattern);
    }
}
