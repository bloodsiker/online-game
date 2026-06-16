<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;

class AdvanceSurvivalWave
{
    public function __construct(
        private readonly DungeonCoordinator $coordinator,
    ) {}

    public function execute(DungeonSession $session, Location $location): void
    {
        $this->coordinator->tryAdvanceSurvivalWave($session, $location);
    }
}
