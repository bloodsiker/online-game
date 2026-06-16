<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class ExpireDungeonSession
{
    public function __construct(
        private readonly DungeonCoordinator $coordinator,
    ) {}

    public function execute(User $user): bool
    {
        return $this->coordinator->expireSessionIfNeeded($user);
    }
}
