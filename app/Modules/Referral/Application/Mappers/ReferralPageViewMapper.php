<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\Mappers;

use App\Models\User;
use App\Modules\Referral\Application\DTOs\ReferralInviteDTO;
use App\Modules\Referral\Application\DTOs\ReferralPageDTO;
use App\Modules\Referral\Application\DTOs\ReferralStageDTO;
use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardStage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class ReferralPageViewMapper
{
    /**
     * @param  Collection<int, Referral>  $referrals
     * @param  Collection<int, ReferralRewardStage>  $stages
     */
    public function map(User $user, ?User $invitedBy, Collection $referrals, Collection $stages): ReferralPageDTO
    {
        $onlineThreshold = CarbonImmutable::now()->subMinutes(10);
        $totalStagesCount = $stages->count();

        return new ReferralPageDTO(
            referralLink: route('register').'?ref='.urlencode($user->name),
            invitedByName: $invitedBy?->name,
            referred: $referrals->map(
                static function (Referral $referral) use ($onlineThreshold, $totalStagesCount): ReferralInviteDTO {
                    $referredUser = $referral->referred;
                    $player = $referredUser?->player;
                    $isOnline = $referredUser?->last_online_at !== null
                        && $referredUser->last_online_at->greaterThan($onlineThreshold);

                    return new ReferralInviteDTO(
                        userId: $referredUser?->id ?? 0,
                        name: $referredUser?->name ?? '—',
                        level: $player?->lvl,
                        isOnline: $isOnline,
                        lastOnlineLabel: $referredUser?->last_online_at?->diffForHumans(),
                        claimedRewardsCount: $referral->claims->count(),
                        totalStagesCount: $totalStagesCount,
                    );
                }
            )->all(),
            stages: $stages->map(
                static fn (ReferralRewardStage $stage): ReferralStageDTO => new ReferralStageDTO(
                    levelThreshold: $stage->level_threshold,
                    rewardType: $stage->reward_type->value,
                    rewardValue: $stage->reward_value,
                    rewardItemName: $stage->rewardItem?->name,
                    description: $stage->description(),
                )
            )->all(),
        );
    }
}
