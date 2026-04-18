<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\DTOs;

final readonly class ExchangeViewItemDTO
{
    public function __construct(
        public int $fromItemId,
        public string $fromItemName,
        public string $fromItemImage,
        public int $toItemId,
        public string $toItemName,
        public string $toItemImage,
        public int $fromAmount,
        public int $toAmount,
        public int $availableCount,
    ) {}
}
