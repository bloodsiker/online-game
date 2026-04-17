<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PlayerLeveledUp;
use App\Modules\Referral\Application\UseCases\GrantReferralRewards;

class GrantReferralReward
{
    public function __construct(private readonly GrantReferralRewards $grantReferralRewards) {}

    public function handle(PlayerLeveledUp $event): void
    {
        $this->grantReferralRewards->handle($event->player);
    }
}
