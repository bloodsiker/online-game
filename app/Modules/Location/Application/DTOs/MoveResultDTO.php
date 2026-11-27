<?php

namespace App\Modules\Location\Application\DTOs;

final class MoveResultDTO
{
    private function __construct(
        public bool $success,
        public ?string $message = null,
        public float $speedModifier = 1.0,
        public ?string $notice = null,
    ) {}

    public static function success(float $speedModifier, ?string $notice = null): self
    {
        return new self(true, null, $speedModifier, $notice);
    }

    public static function blocked(string $message): self
    {
        return new self(false, $message);
    }
}
