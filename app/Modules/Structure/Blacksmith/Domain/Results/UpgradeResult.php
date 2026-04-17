<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Results;

final readonly class UpgradeResult
{
    public function __construct(
        public bool $success,
        public bool $destroyed,
        public string $message,
        public int $newLevel,
    ) {}

    public static function failed(string $message, int $newLevel, bool $destroyed = false): self
    {
        return new self(
            success: false,
            destroyed: $destroyed,
            message: $message,
            newLevel: $newLevel,
        );
    }

    public static function succeeded(string $message, int $newLevel): self
    {
        return new self(
            success: true,
            destroyed: false,
            message: $message,
            newLevel: $newLevel,
        );
    }
}
