<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\UseCases;

use App\Models\User;
use App\Modules\Referral\Application\DTOs\ReferralPageDTO;
use App\Modules\Referral\Application\Mappers\ReferralPageViewMapper;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;

final readonly class GetReferralPage
{
    public function __construct(
        private ReferralRepository $referralRepository,
        private ReferralPageViewMapper $mapper,
    ) {}

    public function handle(User $user): ReferralPageDTO
    {
        return $this->mapper->map(
            user: $user,
            invitedBy: $this->referralRepository->findInviterForUser($user->id),
            referrals: $this->referralRepository->getReferralsByReferrerUserId($user->id),
            stages: $this->referralRepository->getActiveStages(),
        );
    }
}
