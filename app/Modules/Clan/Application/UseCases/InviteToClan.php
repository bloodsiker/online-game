<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class InviteToClan
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, ?string $nickname): void
    {
        if ($nickname === null || $nickname === '') {
            throw new RuntimeException('Введите ник игрока.');
        }

        $this->clanService->invite($user, $nickname);
    }
}
