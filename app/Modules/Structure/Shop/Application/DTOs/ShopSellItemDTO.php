<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopSellItemDTO
{
    public function __construct(
        public int $backpackId,
        public int $itemId,
        public int $count,
        public string $image,
        public string $name,
        public string $color,
        public string $typeName,
        public int $sellPrice,
        public string $infoUrl,
    ) {}
}
