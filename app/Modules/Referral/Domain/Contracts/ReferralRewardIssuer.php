<?php

declare(strict_types=1);

namespace App\Modules\Referral\Domain\Contracts;

interface ReferralRewardIssuer
{
    public function grantGold(int $userId, int $amount): void;

    public function grantDiamonds(int $userId, int $amount): void;

    public function grantItem(int $userId, int $shareItemId, int $count): void;
}
