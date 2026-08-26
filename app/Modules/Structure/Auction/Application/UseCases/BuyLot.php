<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Auction\Application\DTOs\AuctionResultDTO;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use App\Modules\Structure\Auction\Domain\Models\AuctionHistory;
use App\Modules\Structure\Auction\Domain\Models\AuctionSaleProceeds;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class BuyLot
{
    public function execute(User $user, Structure $auction, int $itemId): AuctionResultDTO
    {
        if ($auction->location_id !== $user->location_id) {
            return new AuctionResultDTO(false, 'Вы не находитесь рядом с Комиссионным магазином!');
        }

        $result = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $auction, $itemId, &$result) {
            $lot = Auction::where('structure_id', $auction->id)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (! $lot instanceof Auction) {
                $result = ['ok' => false, 'message' => 'Этого предмета уже нет в продаже'];

                return;
            }

            if ($lot->user_id === $user->id) {
                $result = ['ok' => false, 'message' => 'Нельзя купить собственный лот.'];

                return;
            }

            $freshUser = User::lockForUpdate()->find($user->id);
            if ($freshUser->money < $lot->price) {
                $result = ['ok' => false, 'message' => 'Не достаточно монет для покупки.'];

                return;
            }

            $freshUser->decrement('money', $lot->price);

            $shareItem = $lot->item->itemInfo;
            $existing = $this->findBackpackSlot($user->id, $shareItem->id);

            if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->increment('count', $lot->count);
            } else {
                $user->backpack()->attach($lot->item->id, ['equipped' => 0, 'count' => $lot->count]);
            }

            $history = AuctionHistory::create([
                'buy_user_id' => $user->id,
                'sell_user_id' => $lot->user_id,
                'structure_id' => $lot->structure_id,
                'item_id' => $lot->item_id,
                'count' => $lot->count,
                'price' => $lot->price,
            ]);

            AuctionSaleProceeds::create(['user_id' => $lot->user_id, 'structure_id' => $lot->structure_id, 'auction_history_id' => $history->id, 'amount' => $lot->price]);

            $lot->delete();

            $result = ['ok' => true, 'message' => sprintf('Куплено %s %s шт', $shareItem->name, $lot->count)];
        });

        return new AuctionResultDTO($result['ok'], $result['message']);
    }

    private function findBackpackSlot(int $userId, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $userId)
            ->where('items.share_item_id', $shareItemId)
            ->where('backpacks.equipped', 0)
            ->first();
    }
}
