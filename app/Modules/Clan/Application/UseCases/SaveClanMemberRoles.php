<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class SaveClanMemberRoles
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, array $members): void
    {
        $this->clanService->saveMemberRoles($user, $members);
    }
}
