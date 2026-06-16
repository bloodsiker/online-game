<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class ExitDungeon
{
    public function __construct(
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly DungeonCoordinator $coordinator,
    ) {}

    public function execute(User $user): bool
    {
        if ($this->getActiveDungeonSession->execute($user->id) === null) {
            return false;
        }

        $this->coordinator->exitDungeon($user);

        return true;
    }
}
