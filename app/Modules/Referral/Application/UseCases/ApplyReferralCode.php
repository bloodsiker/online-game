<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\UseCases;

use App\Modules\Referral\Domain\Contracts\ReferralRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class ApplyReferralCode
{
    public function __construct(private ReferralRepository $referralRepository) {}

    public function handle(User $referred, string $referralCode): void
    {
        $referrer = $this->referralRepository->findUserByReferralCode($referralCode);

        if ($referrer === null || $referrer->id === $referred->id) {
            return;
        }

        if ($this->referralRepository->hasReferralForReferredUser($referred->id)) {
            return;
        }

        $this->referralRepository->createReferral($referrer->id, $referred->id);
    }
}
