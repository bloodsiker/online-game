<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use Illuminate\Database\Eloquent\Collection;

class GetMyOrders
{
    /** @return Collection<int, AuctionOrder> */
    public function execute(int $structureId, int $userId): Collection
    {
        return AuctionOrder::where('structure_id', $structureId)
            ->where('user_id', $userId)
            ->with('shareItem')
            ->get();
    }
}