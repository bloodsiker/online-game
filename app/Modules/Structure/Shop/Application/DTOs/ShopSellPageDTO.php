<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopSellPageDTO
{
    /**
     * @param  list<ShopSellItemDTO>  $items
     */
    public function __construct(
        public int $shopId,
        public int $money,
        public int $diamonds,
        public array $items,
        public string $itemTooltipScript,
    ) {}
}
