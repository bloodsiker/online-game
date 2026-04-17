<?php

declare(strict_types=1);

namespace App\Modules\Referral\Domain\Contracts;

use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardStage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface ReferralRepository
{
    public function findUserByName(string $name): ?User;

    public function findReferralByReferredUserId(int $referredUserId): ?Referral;

    public function hasReferralForReferredUser(int $referredUserId): bool;

    public function createReferral(int $referrerUserId, int $referredUserId): void;

    public function getClaimedStageIds(int $referralId): array;

    /**
     * @return Collection<int, ReferralRewardStage>
     */
    public function getUnlockedStages(int $level, array $excludedStageIds): Collection;

    public function createClaim(int $referralId, int $stageId): void;

    public function findInviterForUser(int $userId): ?User;

    /**
     * @return Collection<int, Referral>
     */
    public function getReferralsByReferrerUserId(int $userId): Collection;

    /**
     * @return Collection<int, ReferralRewardStage>
     */
    public function getActiveStages(): Collection;
}
