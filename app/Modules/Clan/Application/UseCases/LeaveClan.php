<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class LeaveClan
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user): void
    {
        $this->clanService->leaveClan($user);
    }
}
