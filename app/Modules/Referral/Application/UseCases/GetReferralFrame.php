<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\UseCases;

use App\Modules\Referral\Application\DTOs\ReferralFrameDTO;
use App\Modules\Referral\Application\Mappers\ReferralFrameViewMapper;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class GetReferralFrame
{
    public function __construct(
        private ReferralRepository $referralRepository,
        private ReferralFrameViewMapper $mapper,
    ) {}

    public function handle(User $user): ReferralFrameDTO
    {
        return $this->mapper->map(
            $this->referralRepository->getReferralsByReferrerUserId($user->id)
        );
    }
}
