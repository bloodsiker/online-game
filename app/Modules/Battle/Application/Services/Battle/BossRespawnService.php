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

    /**
     * Крон: возрождает мировых боссов, у которых наступил respawn_at, не дожидаясь,
     * пока кто-то зайдёт на их локацию. Боссы данжей (локации с dungeon_id) возрождаются
     * по-прежнему в рамках сессии данжа. Возвращает число созданных боссов.
     */
    public function respawnDueWorldBosses(): int
    {
        $locationIds = DB::table('location_has_monsters as lhm')
            ->join('monsters as m', 'm.id', '=', 'lhm.monster_id')
            ->join('locations as l', 'l.id', '=', 'lhm.location_id')
            ->where('m.is_boss', true)
            ->whereNotNull('m.respawn_at')
            ->where('m.respawn_at', '<=', now())
            ->whereNull('l.dungeon_id')
            ->distinct()
            ->pluck('lhm.location_id');

        $created = 0;
        foreach (Location::query()->whereIn('id', $locationIds)->get() as $location) {
            $created += $this->respawnIfDue($location, null);
        }

        return $created;
    }

    public function respawnIfDue(Location $location, ?int $dungeonSessionId): int
    {
        $bosses = $location->monsters()
            ->where('is_boss', true)
            ->get()
            ->filter(static fn ($boss): bool => $boss->canRespawnNow());

        if ($bosses->isEmpty()) {
            return 0;
        }

        // Блокируем строки боссов и перепроверяем respawn_at под блокировкой: два игрока,
        // одновременно вошедшие на локацию, раньше оба видели «босса нет» и создавали
        // по копии (две копии на локации → два объявления об убийстве).
        return DB::transaction(function () use ($bosses, $location, $dungeonSessionId): int {
            $lockedBosses = Monster::query()
                ->whereIn('id', $bosses->pluck('id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->filter(static fn (Monster $boss): bool => $boss->canRespawnNow());

            if ($lockedBosses->isEmpty()) {
                return 0;
            }

            $aliveBossIds = MonsterOnLocation::query()
                ->where('location_id', $location->id)
                ->whereIn('monster_id', $lockedBosses->pluck('id'))
                ->where('active', 1)
                ->when($dungeonSessionId !== null, fn ($query) => $query->where('dungeon_session_id', $dungeonSessionId))
                ->when($dungeonSessionId === null, fn ($query) => $query->whereNull('dungeon_session_id'))
                ->pluck('monster_id')
                ->mapWithKeys(static fn (mixed $monsterId): array => [(int) $monsterId => true]);

            $created = 0;
            foreach ($lockedBosses as $boss) {
                if (! $aliveBossIds->has((int) $boss->id)) {
                    $this->monsterRepo->createMonsterOnLocation($boss, $location, $dungeonSessionId);
                    $aliveBossIds->put((int) $boss->id, true);
                    $created++;
                }
                $boss->clearRespawn();
            }

            return $created;
        });
    }
}
