<?php

declare(strict_types=1);

namespace App\Modules\Referral\Infrastructure\Persistence;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardClaim;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardStage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

final class EloquentReferralRepository implements ReferralRepository
{
    public function findUserByReferralCode(string $code): ?User
    {
        if (preg_match('/^U([1-9][0-9]*)$/i', trim($code), $matches) === 1) {
            return User::query()->find((int) $matches[1]);
        }

        // Preserve referral links already shared with the old nickname format.
        return User::query()->where('name', $code)->first();
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

    public function lockRewardReferral(Referral $observed): ?Referral
    {
        $users = collect([$observed->referrer_user_id, $observed->referred_user_id])->unique()->sort()->mapWithKeys(
            fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->first()],
        );
        $referrer = $users->get($observed->referrer_user_id);
        $referred = $users->get($observed->referred_user_id);
        if ($referrer === null || $referred === null || $referred->player_id === null) {
            return null;
        }
        $player = Player::whereKey($referred->player_id)->where('user_id', $referred->id)->lockForUpdate()->first();
        $referral = Referral::whereKey($observed->id)->where('referrer_user_id', $referrer->id)
            ->where('referred_user_id', $referred->id)->lockForUpdate()->first();
        if ($player === null || $referral === null) {
            return null;
        }
        $referred->setRelation('player', $player);
        $referral->setRelation('referrer', $referrer);
        $referral->setRelation('referred', $referred);

        return $referral;
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
            ->orderBy('stage_id')->lockForUpdate()
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
