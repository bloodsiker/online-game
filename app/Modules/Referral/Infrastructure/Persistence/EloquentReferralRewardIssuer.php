<?php

declare(strict_types=1);

namespace App\Modules\Referral\Infrastructure\Persistence;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Referral\Domain\Contracts\ReferralRewardIssuer;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class EloquentReferralRewardIssuer implements ReferralRewardIssuer
{
    public function grantGold(int $userId, int $amount): void
    {
        $this->credit(userId: $userId, currency: 'money', amount: $amount);
    }

    public function grantDiamonds(int $userId, int $amount): void
    {
        $this->credit(userId: $userId, currency: 'diamond', amount: $amount);
    }

    private function credit(int $userId, string $currency, int $amount): void
    {
        $max = WalletBalanceLimits::MAX_BALANCE;
        if ($amount < 1 || $amount > $max || User::whereKey($userId)->where($currency, '<=', $max - $amount)->increment($currency, $amount) !== 1) {
            throw new \RuntimeException('Реферальная награда превышает допустимый баланс кошелька.');
        }
    }

    public function grantItem(int $userId, int $shareItemId, int $count): void
    {
        $user = User::find($userId);

        if ($user === null) {
            throw new \RuntimeException('Получатель реферальной награды недоступен.');
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
