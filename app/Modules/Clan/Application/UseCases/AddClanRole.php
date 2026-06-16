<?php

declare(strict_types=1);

namespace App\Modules\Clan\Application\UseCases;

use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class AddClanRole
{
    public function __construct(
        private readonly ClanService $clanService,
    ) {}

    public function execute(User $user, ?string $name): void
    {
        if ($name === null || $name === '') {
            throw new RuntimeException('Введите название звания.');
        }

        $this->clanService->addRole($user, $name);
    }
}
