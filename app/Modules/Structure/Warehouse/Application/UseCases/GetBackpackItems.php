<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use Illuminate\Database\Eloquent\Collection;

class GetBackpackItems
{
    /** @return Collection<int, Backpack> */
    public function execute(int $userId): Collection
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->where('equipped', 0)
            ->orderBy('share_items.type', 'desc')
            ->get();
    }
}
