<?php

namespace Mrrh\LicenseClient\Services;

class ApiResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly int $status,
        public readonly array $body,
        public readonly ?string $transportError = null,
    ) {
    }

    public function message(): string
    {
        if ($this->transportError !== null) {
            return $this->transportError;
        }

        return (string) ($this->body['message'] ?? 'The License Server rejected the request.');
    }
}
