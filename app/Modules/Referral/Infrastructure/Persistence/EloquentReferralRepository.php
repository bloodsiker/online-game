<?php

declare(strict_types=1);

namespace App\Modules\Referral\Infrastructure\Persistence;

use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardClaim;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardStage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

final class EloquentReferralRepository implements ReferralRepository
{
    public function findUserByName(string $name): ?User
    {
        return User::where('name', $name)->first();
    }

    public function findReferralByReferredUserId(int $referredUserId): ?Referral
    {
        return Referral::with('referrer')
            ->where('referred_user_id', $referredUserId)
            ->first();
    }

    public function hasReferralForReferredUser(int $referredUserId): bool
    {
        return Referral::where('referred_user_id', $referredUserId)->exists();
    }

    public function createReferral(int $referrerUserId, int $referredUserId): void
    {
        Referral::create([
            'referrer_user_id' => $referrerUserId,
            'referred_user_id' => $referredUserId,
        ]);
    }

    public function getClaimedStageIds(int $referralId): array
    {
        return ReferralRewardClaim::where('referral_id', $referralId)
            ->pluck('stage_id')
            ->all();
    }

    public function getUnlockedStages(int $level, array $excludedStageIds): Collection
    {
        return ReferralRewardStage::where('is_active', true)
            ->where('level_threshold', '<=', $level)
            ->when(
                $excludedStageIds !== [],
                static fn ($query) => $query->whereNotIn('id', $excludedStageIds)
            )
            ->orderBy('level_threshold')
            ->get();
    }

    public function createClaim(int $referralId, int $stageId): void
    {
        ReferralRewardClaim::create([
            'referral_id' => $referralId,
            'stage_id' => $stageId,
        ]);
    }

    public function findInviterForUser(int $userId): ?User
    {
        return Referral::where('referred_user_id', $userId)
            ->with('referrer')
            ->first()?->referrer;
    }

    public function getReferralsByReferrerUserId(int $userId): Collection
    {
        return Referral::where('referrer_user_id', $userId)
            ->with(['referred.player', 'claims.stage'])
            ->get();
    }

    public function getActiveStages(): Collection
    {
        return ReferralRewardStage::where('is_active', true)
            ->with('rewardItem')
            ->orderBy('level_threshold')
            ->get();
    }
}
