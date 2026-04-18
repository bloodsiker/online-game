<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Auction\Application\DTOs\AuctionResultDTO;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use App\Modules\Structure\Auction\Domain\Services\AuctionFeeCalculator;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class CreateLot
{
    public function __construct(
        private readonly AuctionFeeCalculator $feeCalculator,
    ) {}

    public function execute(
        User $user,
        Structure $auction,
        int $itemId,
        int $amount,
        int $price,
        bool $isAnonymous,
    ): AuctionResultDTO {
        $fee = $this->feeCalculator->calculate($price);
        if ($user->money < $fee) {
            return new AuctionResultDTO(false, 'Не достаточно монет что бы оплатить налог.');
        }

        $result = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $auction, $itemId, $amount, $price, $fee, $isAnonymous, &$result) {
            $slotItem = Backpack::where('item_id', $itemId)
                ->where('user_id', $user->id)
                ->where('equipped', 0)
                ->lockForUpdate()
                ->with('item.itemInfo')
                ->first();

            if (! $slotItem instanceof Backpack) {
                $result = ['ok' => false, 'message' => 'Не найдено предмета в сумке.'];

                return;
            }

            $qty = min($amount, $slotItem->count);

            Auction::create([
                'user_id' => $user->id,
                'structure_id' => $auction->id,
                'item_id' => $slotItem->item->id,
                'count' => $qty,
                'is_anonymous' => $isAnonymous,
                'price' => $price,
            ]);

            if ($slotItem->count <= $qty) {
                $slotItem->delete();
            } else {
                $slotItem->decrement('count', $qty);
            }

            $user->decrement('money', $fee);

            $result = ['ok' => true, 'message' => sprintf('%s выставлен на продажу', $slotItem->item->itemInfo->name)];
        });

        return new AuctionResultDTO($result['ok'], $result['message']);
    }
}
