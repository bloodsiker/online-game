<?php

declare(strict_types=1);

namespace App\Services;

final readonly class CastAttemptResult
{
    private function __construct(
        public bool $ok,
        public ?string $reason,
    ) {}

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $reason): self
    {
        return new self(false, $reason);
    }
}
