<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Application\DTOs\AuctionResultDTO;
use App\Modules\Structure\Auction\Domain\Models\AuctionSaleProceeds;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class TakeSaleProceeds
{
    public function execute(User $user, Structure $auction, int $proceedsId): AuctionResultDTO
    {
        return DB::transaction(function () use ($user, $auction, $proceedsId): AuctionResultDTO {
            $structureIds = Structure::where('location_id', $auction->location_id)->pluck('id');
            $proceeds = AuctionSaleProceeds::where('id', $proceedsId)->where('user_id', $user->id)
                ->whereIn('structure_id', $structureIds)->lockForUpdate()->first();
            if (! $proceeds) return new AuctionResultDTO(false, 'Выручка от продажи не найдена.');
            User::lockForUpdate()->findOrFail($user->id)->increment('money', $proceeds->amount);
            $amount = $proceeds->amount; $proceeds->delete();
            return new AuctionResultDTO(true, "Получено {$amount} монет за продажу.");
        });
    }
}
