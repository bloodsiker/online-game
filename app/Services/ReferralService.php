<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReferralRewardType;
use App\Models\Item\Item;
use App\Models\Player\Player;
use App\Models\Referral\Referral;
use App\Models\Referral\ReferralRewardClaim;
use App\Models\Referral\ReferralRewardStage;
use App\Models\User;

class ReferralService
{
    /**
     * Links a newly registered user to the referrer identified by their nickname.
     */
    public function applyCode(User $referred, string $refName): void
    {
        $referrer = User::where('name', $refName)->first();

        if (!$referrer || $referrer->id === $referred->id) {
            return;
        }

        Referral::create([
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $referred->id,
        ]);
    }

    /**
     * Called on PlayerLeveledUp — grants all unlocked, unclaimed stage rewards to the referrer.
     */
    public function checkAndGrantRewards(Player $player): void
    {
        $referral = Referral::where('referred_user_id', $player->user_id)->first();
        if (!$referral) {
            return;
        }

        $claimedStageIds = ReferralRewardClaim::where('referral_id', $referral->id)
            ->pluck('stage_id');

        $stages = ReferralRewardStage::where('is_active', true)
            ->where('level_threshold', '<=', $player->lvl)
            ->whereNotIn('id', $claimedStageIds)
            ->orderBy('level_threshold')
            ->get();

        if ($stages->isEmpty()) {
            return;
        }

        $referrer = $referral->referrer;

        foreach ($stages as $stage) {
            $this->grantReward($referrer, $stage);

            ReferralRewardClaim::create([
                'referral_id' => $referral->id,
                'stage_id'    => $stage->id,
            ]);
        }
    }

    /**
     * Returns referral stats for a player: who invited them, who they invited, reward progress.
     */
    public function getPlayerData(User $user): array
    {
        $referralLink = route('register') . '?ref=' . urlencode($user->name);

        // Кто пригласил этого игрока
        $invitedBy = Referral::where('referred_user_id', $user->id)
            ->with('referrer')
            ->first()?->referrer;

        // Кого пригласил этот игрок
        $referred = Referral::where('referrer_user_id', $user->id)
            ->with(['referred.player', 'claims.stage'])
            ->get();

        // Все активные этапы для отображения прогресса
        $stages = ReferralRewardStage::where('is_active', true)
            ->orderBy('level_threshold')
            ->with('rewardItem')
            ->get();

        return compact('referralLink', 'invitedBy', 'referred', 'stages');
    }

    private function grantReward(User $referrer, ReferralRewardStage $stage): void
    {
        match ($stage->reward_type) {
            ReferralRewardType::GOLD    => $referrer->increment('money', $stage->reward_value),
            ReferralRewardType::DIAMOND => $referrer->increment('diamond', $stage->reward_value),
            ReferralRewardType::ITEM    => $this->grantItem($referrer, $stage),
        };
    }

    private function grantItem(User $referrer, ReferralRewardStage $stage): void
    {
        if (!$stage->reward_item_id) {
            return;
        }

        $item = new Item();
        $item->share_item_id = $stage->reward_item_id;
        $item->save();

        $referrer->backpack()->attach($item->id, [
            'equipped' => false,
            'count'    => $stage->reward_value,
        ]);
    }
}