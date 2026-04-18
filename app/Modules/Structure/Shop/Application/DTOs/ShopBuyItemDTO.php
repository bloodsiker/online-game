<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopBuyItemDTO
{
    public function __construct(
        public int $shareItemId,
        public string $name,
        public string $image,
        public string $typeName,
        public int $price,
        public string $infoUrl,
        public string $buyUrl,
    ) {}
}
