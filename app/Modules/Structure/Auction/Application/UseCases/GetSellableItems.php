<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Auction\Application\Services\AuctionItemSelectionFilter;
use Illuminate\Database\Eloquent\Collection;

class GetSellableItems
{
    public function __construct(private readonly AuctionItemSelectionFilter $filter) {}

    /** @return Collection<int, Backpack> */
    public function execute(int $userId, array $filter = []): Collection
    {
        $query = Backpack::select('backpacks.*')
            ->with(['item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->where('backpacks.equipped', 0)
            ->where('share_items.is_auction_sellable', 1);

        return $this->filter->apply($query, $filter)->orderBy('items.share_item_id', 'desc')->get();
    }
}
