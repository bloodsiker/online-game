<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Auction\Application\DTOs\AuctionResultDTO;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class CancelLot
{
    public function execute(User $user, Structure $auction, int $slotId): AuctionResultDTO
    {
        $result = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $slotId, &$result) {
            $slot = Auction::where('id', $slotId)
                ->where('user_id', $user->id)
                ->where('structure_id', $auction->id)
                ->lockForUpdate()
                ->first();

            if (! $slot instanceof Auction) {
                $result = ['ok' => false, 'message' => 'Лот не найден.'];

                return;
            }

            $shareItem = $slot->item->itemInfo;
            $existing = $this->findBackpackSlot($user->id, $shareItem->id);

            if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->increment('count', $slot->count);
            } else {
                $user->backpack()->attach($slot->item->id, ['equipped' => 0, 'count' => $slot->count]);
            }

            $slot->delete();

            $result = ['ok' => true, 'message' => sprintf('Вы забрали %s %s шт', $shareItem->name, $slot->count)];
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
