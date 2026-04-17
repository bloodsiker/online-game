<?php

declare(strict_types=1);

namespace App\Modules\Referral\Application\UseCases;

use App\Models\User;
use App\Modules\Referral\Domain\Contracts\ReferralRepository;

final readonly class ApplyReferralCode
{
    public function __construct(private ReferralRepository $referralRepository) {}

    public function handle(User $referred, string $refName): void
    {
        $referrer = $this->referralRepository->findUserByName($refName);

        if ($referrer === null || $referrer->id === $referred->id) {
            return;
        }

        if ($this->referralRepository->hasReferralForReferredUser($referred->id)) {
            return;
        }

        $this->referralRepository->createReferral($referrer->id, $referred->id);
    }
}
