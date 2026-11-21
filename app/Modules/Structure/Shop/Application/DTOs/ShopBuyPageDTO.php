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
        public ?string $shopDescription,
        public int $money,
        public int $diamonds,
        public array $items,
        public array $categories,
        public ?int $activeCategoryId,
        public ShopCartDTO $cart,
        public string $itemTooltipScript,
        public ShopFilterDTO $filter = new ShopFilterDTO,
        public int $totalItemsCount = 0,
        public int $playerLevel = 0,
        /** @var array<int, int> Сколько предметов-требований (бартер) у игрока в сумке: share_item_id => count */
        public array $ownedRequirementCounts = [],
    ) {}
}
