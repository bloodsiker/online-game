<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Domain\Models\AuctionSaleProceeds;
use Illuminate\Database\Eloquent\Collection;

class GetSaleProceeds
{
    /** @return Collection<int, AuctionSaleProceeds> */
    /** @param int[] $structureIds */
    public function execute(array $structureIds, int $userId): Collection
    {
        return AuctionSaleProceeds::where('user_id', $userId)->whereIn('structure_id', $structureIds)
            ->with('history.item.itemInfo')->latest()->get();
    }
}
