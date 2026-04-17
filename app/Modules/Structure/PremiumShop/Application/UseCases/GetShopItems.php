<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Application\UseCases;

use App\Models\Shop\ShopItem;
use Illuminate\Database\Eloquent\Collection;

class GetShopItems
{
    /** @return Collection<int, ShopItem> */
    public function execute(int $structureId, int $categoryId): Collection
    {
        return ShopItem::select('shop_items.*')
            ->with(['item', 'requirements.item'])
            ->where('shop_items.structure_id', $structureId)
            ->where('share_structure_category_id', $categoryId)
            ->orderBy('shop_items.sort_order', 'desc')
            ->get();
    }
}