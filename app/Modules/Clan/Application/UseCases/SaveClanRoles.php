<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class SaveClanRoles
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, array $grades): void
    {
        $this->clanService->saveRoles($user, $grades);
    }
}
