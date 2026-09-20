<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Modules\Item\Domain\DTOs\InstantRewardResult;
use App\Modules\Item\Domain\Enums\InstantRewardType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;

/**
 * Выдаёт награду по шаблону ShareItem без создания предмета в рюкзаке.
 * Источник награды (сундук, шкатулка, тайник на локации) передаёт уже
 * рассчитанное количество, поэтому сервис не зависит от конкретной механики.
 */
class InstantItemRewardService
{
    public function grant(User $user, ShareItem $shareItem, int $amount): ?InstantRewardResult
    {
        $config = $shareItem->instantReward;
        if ($config === null) {
            return null;
        }

        $amount = max(1, $amount);

        return match ($config->reward_type) {
            InstantRewardType::MONEY => $this->grantMoney($user, $shareItem, $amount),
        };
    }

    private function grantMoney(User $user, ShareItem $shareItem, int $amount): InstantRewardResult
    {
        // SQL increment не теряет начисления при одновременном получении наград.
        $user->increment('money', $amount);

        return new InstantRewardResult(
            type: InstantRewardType::MONEY,
            amount: $amount,
            shareItemId: (int) $shareItem->id,
            name: (string) $shareItem->name,
            image: (string) $shareItem->image,
        );
    }
}
