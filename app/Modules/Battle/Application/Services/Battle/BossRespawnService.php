<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Repositories\MonsterOnLocationRepository;

/**
 * Боси не підпорядковані загальній percent/count-механіці респауну —
 * після смерті боса respawn_at виставляється в Monster::scheduleRespawn(),
 * і як тільки цей момент настав, тут заново створюється MonsterOnLocation
 * на локації, до якої бос прив'язаний через location_has_monsters.
 *
 * Викликається як з BattleService (вхід через рух/ворота), так і з
 * MonsterSpawner (звичайний перегляд поточної локації) — обидва шляхи
 * мають незалежну логіку входу на локацію в цій кодовій базі.
 */
readonly class BossRespawnService
{
    public function __construct(
        private MonsterOnLocationRepository $monsterRepo,
    ) {}

    public function respawnIfDue(Location $location, ?int $dungeonSessionId): void
    {
        $bosses = $location->monsters()->where('is_boss', true)->get();

        foreach ($bosses as $boss) {
            if (! $boss->canRespawnNow()) {
                continue;
            }

            $alreadyAlive = MonsterOnLocation::where('location_id', $location->id)
                ->where('monster_id', $boss->id)
                ->where('active', 1)
                ->when($dungeonSessionId !== null, fn ($q) => $q->where('dungeon_session_id', $dungeonSessionId))
                ->when($dungeonSessionId === null, fn ($q) => $q->whereNull('dungeon_session_id'))
                ->exists();

            if ($alreadyAlive) {
                continue;
            }

            $this->monsterRepo->createMonsterOnLocation($boss, $location, $dungeonSessionId);
            $boss->clearRespawn();
        }
    }
}