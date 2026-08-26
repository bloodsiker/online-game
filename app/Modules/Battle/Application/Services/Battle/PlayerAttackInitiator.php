<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

readonly class PlayerAttackInitiator
{
    public function __construct(
        private BattleRepository $battleRepository,
    ) {}

    public function attack(Location $location, int $monsterId): ?Battle
    {
        $monster = MonsterOnLocation::with('monster')
            ->where('id', $monsterId)
            ->where('active', 1)
            ->first();

        if (! $monster) {
            return null;
        }

        $battle = $this->battleRepository->createBattle($location);
        $user = auth()->user();

        $this->battleRepository->createBattleDetails($battle, $user);
        $this->battleRepository->createBattleDetails($battle, null, $monster);

        $action = sprintf('<p>Вы напали на врага - <b>%s...</b></p>', $monster->monster->name);
        $this->battleRepository->createBattleRound($battle, $action, $user);

        return $battle;
    }
}
