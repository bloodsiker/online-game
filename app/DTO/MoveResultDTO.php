<?php

namespace App\DTO;

final class MoveResultDTO
{
    private function __construct(
        public bool $success,
        public ?string $message = null,
        public float $speedModifier = 1.0,
    ) {}

    public static function success(float $speedModifier): self
    {
        return new self(true, null, $speedModifier);
    }

    public static function blocked(string $message): self
    {
        return new self(false, $message);
    }
}
