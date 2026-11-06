<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;

readonly class PlayerAttackInitiator
{
    public function __construct(private BattleService $battleService) {}

    public function attack(Location $location, int $monsterId): ?Battle
    {
        return $this->battleService->attackMonster($location, $monsterId);
    }
}
