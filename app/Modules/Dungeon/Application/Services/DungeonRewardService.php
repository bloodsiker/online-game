<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Dungeon\Domain\Enums\DungeonRewardType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class DungeonRewardService
{
    public function __construct(
        private BackpackService $backpackService,
        private ExperienceService $experienceService,
    ) {}

    public function grant(Dungeon $dungeon, User $user): void
    {
        $user->loadMissing('player');

        foreach ($dungeon->rewards()->with('shareItem')->get() as $reward) {
            if ((mt_rand(0, 100000) / 1000) > $reward->drop_chance) {
                continue;
            }

            $amount = $reward->randomAmount();

            match ($reward->type) {
                DungeonRewardType::GOLD => $this->credit($user, 'money', $amount),
                DungeonRewardType::DIAMOND => $this->credit($user, 'diamond', $amount),
                DungeonRewardType::EXPERIENCE => $user->player->increment(
                    'exp',
                    $this->experienceService->calculateGain($user->player, $amount),
                ),
                DungeonRewardType::ITEM => $this->backpackService->addItemByShareItem($user, $reward->shareItem, $amount),
            };
        }
    }

    private function credit(User $user, string $currency, int $amount): void
    {
        $max = WalletBalanceLimits::MAX_BALANCE;
        if ($amount < 0 || $amount > $max || ($amount > 0 && User::whereKey($user->id)->where($currency, '<=', $max - $amount)->increment($currency, $amount) !== 1)) {
            throw new \RuntimeException('Награда превышает допустимый баланс кошелька.');
        }
    }
}
