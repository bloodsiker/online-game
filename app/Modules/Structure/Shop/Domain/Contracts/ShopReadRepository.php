<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Domain\Contracts;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Support\Collection;

interface ShopReadRepository
{
    public function findStructureOrFail(int $id): Structure;

    /**
     * @return Collection<int, ShopItem>
     */
    public function getShopItems(int $structureId): Collection;

    public function findShareItem(int $shareItemId): ?ShareItem;

    public function findResourceBackpackItem(int $userId, int $shareItemId): ?Backpack;

    /**
     * @return Collection<int, Backpack>
     */
    public function getSellableItems(int $userId): Collection;

    /**
     * @param  list<int>  $itemIds
     * @return Collection<int, Backpack>
     */
    public function getSelectedSellableItems(int $userId, array $itemIds): Collection;
}
