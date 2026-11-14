<?php

declare(strict_types=1);

namespace App\Modules\Gift\Application\DTOs;

final readonly class SendGiftResult
{
    public function __construct(
        public bool $ok,
        public string $message,
        public int $remainingCount = 0,
    ) {}

    public static function fail(string $message): self
    {
        return new self(ok: false, message: $message);
    }
}
