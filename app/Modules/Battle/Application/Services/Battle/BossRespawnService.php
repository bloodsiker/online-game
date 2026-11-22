<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Battle;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Monster\Infrastructure\Persistence\MonsterOnLocationRepository;
use Illuminate\Support\Facades\DB;

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
        $bosses = $location->monsters()
            ->where('is_boss', true)
            ->get()
            ->filter(static fn ($boss): bool => $boss->canRespawnNow());

        if ($bosses->isEmpty()) {
            return;
        }

        // Блокируем строки боссов и перепроверяем respawn_at под блокировкой: два игрока,
        // одновременно вошедшие на локацию, раньше оба видели «босса нет» и создавали
        // по копии (две копии на локации → два объявления об убийстве).
        DB::transaction(function () use ($bosses, $location, $dungeonSessionId): void {
            $lockedBosses = Monster::query()
                ->whereIn('id', $bosses->pluck('id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->filter(static fn (Monster $boss): bool => $boss->canRespawnNow());

            if ($lockedBosses->isEmpty()) {
                return;
            }

            $aliveBossIds = MonsterOnLocation::query()
                ->where('location_id', $location->id)
                ->whereIn('monster_id', $lockedBosses->pluck('id'))
                ->where('active', 1)
                ->when($dungeonSessionId !== null, fn ($query) => $query->where('dungeon_session_id', $dungeonSessionId))
                ->when($dungeonSessionId === null, fn ($query) => $query->whereNull('dungeon_session_id'))
                ->pluck('monster_id')
                ->mapWithKeys(static fn (mixed $monsterId): array => [(int) $monsterId => true]);

            foreach ($lockedBosses as $boss) {
                if (! $aliveBossIds->has((int) $boss->id)) {
                    $this->monsterRepo->createMonsterOnLocation($boss, $location, $dungeonSessionId);
                    $aliveBossIds->put((int) $boss->id, true);
                }
                $boss->clearRespawn();
            }
        });
    }
}
