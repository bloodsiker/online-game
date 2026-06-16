<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class KickClanMember
{
    public function __construct(
        private readonly ClanService $clanService,
        private readonly ClanSkillService $clanSkillService,
    ) {}

    public function execute(User $user, User $target): void
    {
        $clan = $user->clanMembership?->clan;
        $targetPlayer = $target->player;

        $this->clanService->kickMember($user, $target);

        if ($clan !== null && $targetPlayer !== null) {
            $this->clanSkillService->removeAllSkillsFromPlayer($targetPlayer, $clan);
        }
    }
}
