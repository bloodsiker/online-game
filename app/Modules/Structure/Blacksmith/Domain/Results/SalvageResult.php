<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Results;

final readonly class SalvageResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $crystalCount = 0,
    ) {}

    public static function itemNotBreakable(): self
    {
        return new self(
            success: false,
            message: 'Предмет нельзя разбить на кристаллы.',
        );
    }

    public static function success(int $crystalCount): self
    {
        return new self(
            success: true,
            message: sprintf('Вы получили кристаллов в количестве %s шт', $crystalCount),
            crystalCount: $crystalCount,
        );
    }
}
