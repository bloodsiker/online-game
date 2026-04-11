<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DungeonRewardType;
use App\Models\Dungeon\DungeonReward;
use App\Models\Dungeon\DungeonRun;
use App\Models\User;
use App\Repositories\DungeonPityRepository;
use App\Repositories\DungeonRunRepository;
use Illuminate\Support\Collection;

class DungeonRewardService
{
    public function __construct(
        private readonly BackpackService       $backpackService,
        private readonly DungeonRunRepository  $runRepository,
        private readonly DungeonPityRepository $pityRepository,
    ) {}

    /**
     * Distribute all rewards to all active participants.
     *
     * @param Collection<int, User> $participants
     */
    public function distribute(DungeonRun $run, Collection $participants): void
    {
        $rewards = $run->dungeon->rewards;

        foreach ($participants as $user) {
            $failCount = $this->pityRepository->getFailCount($run->dungeon_id, $user->id);

            foreach ($rewards as $reward) {
                $this->giveReward($run, $reward, $user, $failCount);
            }
        }
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function giveReward(DungeonRun $run, DungeonReward $reward, User $user, int $failCount): void
    {
        $isPityTriggered = $reward->hasPity() && $failCount >= $reward->pity_threshold;

        if ($reward->is_pity_only && ! $isPityTriggered) {
            return;
        }

        if (! $isPityTriggered && ! $this->rollChance($reward->drop_chance)) {
            return;
        }

        $amount = $reward->randomAmount();

        match ($reward->type) {
            DungeonRewardType::GOLD       => $this->giveGold($user, $amount),
            DungeonRewardType::DIAMOND    => $this->giveDiamond($user, $amount),
            DungeonRewardType::ITEM       => $this->giveItem($user, $reward, $amount),
            DungeonRewardType::EXPERIENCE => $this->giveExperience($user, $amount),
        };

        $this->runRepository->log($run->id, $user->id, 'reward_given', [
            'type'             => $reward->type->value,
            'amount'           => $amount,
            'pity_triggered'   => $isPityTriggered,
        ]);
    }

    private function rollChance(float $chance): bool
    {
        return (mt_rand(0, 10000) / 100) < $chance;
    }

    private function giveGold(User $user, int $amount): void
    {
        $user->increment('money', $amount);
    }

    private function giveDiamond(User $user, int $amount): void
    {
        $user->increment('diamond', $amount);
    }

    private function giveItem(User $user, DungeonReward $reward, int $amount): void
    {
        if ($reward->shareItem === null) {
            return;
        }

        $this->backpackService->addItemByShareItem($user, $reward->shareItem, $amount);
    }

    private function giveExperience(User $user, int $amount): void
    {
        $player = $user->player;
        $player->increment('exp', $amount);
    }
}