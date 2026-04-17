<?php

declare(strict_types=1);

namespace App\Modules\Referral\Infrastructure\Persistence;

use App\Models\Item\Item;
use App\Models\User;
use App\Modules\Referral\Domain\Contracts\ReferralRewardIssuer;

final class EloquentReferralRewardIssuer implements ReferralRewardIssuer
{
    public function grantGold(int $userId, int $amount): void
    {
        User::whereKey($userId)->increment('money', $amount);
    }

    public function grantDiamonds(int $userId, int $amount): void
    {
        User::whereKey($userId)->increment('diamond', $amount);
    }

    public function grantItem(int $userId, int $shareItemId, int $count): void
    {
        $user = User::find($userId);

        if ($user === null) {
            return;
        }

        $item = new Item;
        $item->share_item_id = $shareItemId;
        $item->save();

        $user->backpack()->attach($item->id, [
            'equipped' => false,
            'count' => $count,
        ]);
    }
}
