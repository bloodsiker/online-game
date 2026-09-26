<?php

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Dungeon\Application\UseCases\GetActiveDungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

readonly class PlayerAttackInitiator
{
    public function __construct(
        private BattleRepository $battleRepository,
        private GetActiveDungeonSession $getActiveDungeonSession,
    ) {}

    public function attack(Location $location, int $monsterId): ?Battle
    {
        $user = auth()->user();
        $session = $location->dungeon_id !== null
            ? $this->getActiveDungeonSession->execute($user->id)
            : null;

        $monster = MonsterOnLocation::with('monster')
            ->where('id', $monsterId)
            ->where('location_id', $location->id)
            ->where('active', 1)
            ->when(
                $session !== null,
                fn ($query) => $query->where('dungeon_session_id', $session->monsterSessionId()),
                fn ($query) => $query->whereNull('dungeon_session_id'),
            )
            ->first();

        if (! $monster) {
            return null;
        }

        $battle = $this->battleRepository->createBattle($location, $session?->dungeon_run_id);

        $this->battleRepository->createBattleDetails($battle, $user);
        $this->battleRepository->createBattleDetails($battle, null, $monster);

        $action = sprintf('<p>Вы напали на врага - <b>%s...</b></p>', $monster->monster->name);
        $this->battleRepository->createBattleRound($battle, $action, $user);

        return $battle;
    }
}
