<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Structure\Auction\Application\DTOs\AuctionResultDTO;
use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrder
{
    public function execute(
        User $user,
        Structure $auction,
        int $shareItemId,
        int $count,
        int $price,
        bool $isAnonymous,
    ): AuctionResultDTO {
        $shareItem = ShareItem::where('id', $shareItemId)->where('is_sell', 1)->first();
        if (! $shareItem) {
            return new AuctionResultDTO(false, 'Предмет не найден.');
        }

        $totalCost = 100 + ($count * $price);
        $result    = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $auction, $shareItem, $count, $price, $totalCost, $isAnonymous, &$result) {
            $freshUser = User::lockForUpdate()->find($user->id);

            if ($freshUser->money < $totalCost) {
                $result = ['ok' => false, 'message' => sprintf(
                    'Недостаточно монет. Нужно %d (100 комиссия + %d эскроу).',
                    $totalCost,
                    $count * $price,
                )];
                return;
            }

            $freshUser->decrement('money', $totalCost);

            AuctionOrder::create([
                'user_id'       => $user->id,
                'structure_id'  => $auction->id,
                'share_item_id' => $shareItem->id,
                'count'         => $count,
                'price'         => $price,
                'is_anonymous'  => $isAnonymous,
            ]);

            $result = ['ok' => true, 'message' => sprintf(
                'Заявка на покупку «%s» (%d шт. по %d) создана',
                $shareItem->name, $count, $price,
            )];
        });

        return new AuctionResultDTO($result['ok'], $result['message']);
    }
}