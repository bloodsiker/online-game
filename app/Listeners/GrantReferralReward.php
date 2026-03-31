<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PlayerLeveledUp;
use App\Services\ReferralService;

class GrantReferralReward
{
    public function __construct(private readonly ReferralService $referralService) {}

    public function handle(PlayerLeveledUp $event): void
    {
        $this->referralService->checkAndGrantRewards($event->player);
    }
}