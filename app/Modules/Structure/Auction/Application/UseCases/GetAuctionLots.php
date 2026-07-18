<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Domain\Models\Auction;
use Illuminate\Database\Eloquent\Collection;

class GetAuctionLots
{
    /** @return Collection<int, Auction> */
    public function execute(int $structureId, ?int $userId = null): Collection
    {
        $query = Auction::where('structure_id', $structureId)
            ->with(['item.itemInfo', 'user.player', 'user.clanMembership.clan']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }
}
