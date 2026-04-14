<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Enums\ShareItemType;
use App\Modules\Structure\Auction\Domain\Models\AuctionClaim;
use App\Models\Backpack;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TakeClaim
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function execute(User $user, int $claimId): array
    {
        $result = ['ok' => true, 'message' => ''];

        DB::transaction(function () use ($user, $claimId, &$result) {
            $claim = AuctionClaim::where('id', $claimId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $claim instanceof AuctionClaim) {
                $result = ['ok' => false, 'message' => 'Предмет не найден.'];
                return;
            }

            $shareItem = $claim->item->itemInfo;
            $existing  = $this->findBackpackSlot($user->id, $shareItem->id);

            if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->increment('count', $claim->count);
            } else {
                $user->backpack()->attach($claim->item_id, ['equipped' => 0, 'count' => $claim->count]);
            }

            $claim->delete();

            $result = ['ok' => true, 'message' => ''];
        });

        return $result;
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