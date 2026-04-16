<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Domain\Models\AuctionClaim;
use Illuminate\Database\Eloquent\Collection;

class GetClaims
{
    /** @return Collection<int, AuctionClaim> */
    public function execute(int $structureId, int $userId): Collection
    {
        return AuctionClaim::where('user_id', $userId)
            ->where('structure_id', $structureId)
            ->with('item.itemInfo')
            ->get();
    }
}