<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Domain\Models\Auction;

class GetLotForEdit
{
    public function execute(int $slotId, int $userId): Auction
    {
        return Auction::where('id', $slotId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
