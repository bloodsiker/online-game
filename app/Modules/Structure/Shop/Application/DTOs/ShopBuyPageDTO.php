<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\DTOs;

final readonly class ShopBuyPageDTO
{
    /**
     * @param  list<ShopBuyItemDTO>  $items
     * @param  list<array{id: int, name: string}>  $categories
     */
    public function __construct(
        public int $shopId,
        public string $shopType,
        public string $shopName,
        public int $money,
        public int $diamonds,
        public array $items,
        public array $categories,
        public ?int $activeCategoryId,
        public ShopCartDTO $cart,
        public string $itemTooltipScript,
    ) {}
}
