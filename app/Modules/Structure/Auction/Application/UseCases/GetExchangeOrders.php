<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Application\DTOs\ExchangeFilterDTO;
use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use App\Modules\Backpack\Domain\Models\Backpack;
use Illuminate\Database\Eloquent\Collection;

class GetExchangeOrders
{
    /** @return Collection<int, AuctionOrder> */
    public function execute(int $structureId, int $userId, ExchangeFilterDTO $filter): Collection
    {
        $query = AuctionOrder::where('structure_id', $structureId)
            ->where('user_id', '!=', $userId)
            ->with(['shareItem', 'user']);

        if ($filter->matching) {
            $userShareItemIds = Backpack::select('items.share_item_id')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $userId)
                ->where('backpacks.equipped', 0)
                ->where('share_items.is_sell', 1)
                ->pluck('items.share_item_id');

            $query->whereIn('share_item_id', $userShareItemIds);
        }

        if ($filter->name !== null) {
            $query->whereHas('shareItem', fn ($q) => $q->where('name', 'like', '%' . $filter->name . '%'));
        }

        if ($filter->type !== null) {
            $query->whereHas('shareItem', fn ($q) => $q->where('type', $filter->type));
        }

        if ($filter->countMin !== null) {
            $query->where('count', '>=', $filter->countMin);
        }

        if ($filter->countMax !== null) {
            $query->where('count', '<=', $filter->countMax);
        }

        return $query->orderByDesc('price')->get();
    }
}