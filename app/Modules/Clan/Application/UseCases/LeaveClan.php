<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class LeaveClan
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ClanSkillService $clanSkillService,
    ) {}

    public function execute(User $user): void
    {
        $membership = $user->clanMembership;
        $clan = $membership?->clan;
        $player = $user->player;

        $this->clanService->leaveClan($user);

        if ($clan !== null && $player !== null) {
            $this->clanSkillService->removeAllSkillsFromPlayer($player, $clan);
        }
    }
}
