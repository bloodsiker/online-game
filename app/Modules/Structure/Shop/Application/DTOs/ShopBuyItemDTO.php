<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopBuyItemDTO
{
    public function __construct(
        public int $shopItemId,
        public int $itemId,
        public string $name,
        public string $color,
        public string $image,
        public string $typeName,
        public int $price,
        public int $diamond,
        public string $infoUrl,
    ) {}
}
