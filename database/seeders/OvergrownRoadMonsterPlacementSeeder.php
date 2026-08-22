<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет монстров OvergrownRoadMonsterSeeder по локациям «Заросшей
 * дороги» (992-1115). Карта делится пополам по уровню, не по виду —
 * каждая половина получает пул из ВСЕХ ЧЕТЫРЁХ ролей своего уровневого
 * диапазона (не по одному виду на локацию), чтобы игрок встречал разные
 * контры на каждом шаге, а не проходил чисто «зону волка», потом «зону
 * вепря» и т.д. — та же логика пула, что уже в Забытом Кургане
 * (location_has_monsters на одну локацию Забытого Кургана содержит 4-5
 * видов, спавнер сам выбирает случайно из пула — см. MonsterSpawner::
 * spawnNewAndGetAggressive).
 *
 * 992-1049 (58 локаций, первая половина) → уровни 25-30:
 *   Терновый Волк(25), Вепрь(26), Терновый Сорокопут(27), Хищный Плющ(28),
 *   Терновый Волк(29), Терновый Сорокопут(30)
 * 1050-1115 (66 локаций, вторая половина) → уровни 31-36:
 *   Вепрь(31), Хищный Плющ(32), Терновый Волк(33), Терновый Сорокопут(34),
 *   Вепрь(35), Хищный Плющ(36)
 *
 * pivot location_has_monsters.aggression оставляем NULL: MonsterOnLocation::
 * getAggression() откатывается на monster.aggression, если override не
 * задан — дублировать значение здесь незачем (в Забытом Кургане оно
 * задублировано explicit=90, это наследие, а не требование).
 *
 * count_monster/percent_respawn_monster/time_not_attack были 0 у всех 124
 * локаций (карта только что создана) — без них MonsterSpawner::canRespawn()/
 * shouldSpawn() никогда не заспавнят ни одного моба, сколько бы видов ни
 * лежало в location_has_monsters. Значения percent_respawn_monster=70 и
 * time_not_attack=15 — как в Забытом Кургане. count_monster циклится
 * 1,2,1,2,3 по location_id % 5 — детерминированно (не mt_rand, чтобы повторный
 * прогон seeder'а был воспроизводим), даёт похожее распределение на структуру
 * Забытого Кургана (1: ~42%, 2: ~46%, 3: ~12% там; тут 1/2: по 40%, 3: 20%).
 */
class OvergrownRoadMonsterPlacementSeeder extends Seeder
{
    private const FIRST_HALF_RANGE = [992, 1049];

    private const SECOND_HALF_RANGE = [1050, 1115];

    private const FIRST_HALF_MONSTERS = [
        ['Терновый Волк', 25],
        ['Вепрь', 26],
        ['Терновый Сорокопут', 27],
        ['Хищный Плющ', 28],
        ['Терновый Волк', 29],
        ['Терновый Сорокопут', 30],
    ];

    private const SECOND_HALF_MONSTERS = [
        ['Вепрь', 31],
        ['Хищный Плющ', 32],
        ['Терновый Волк', 33],
        ['Терновый Сорокопут', 34],
        ['Вепрь', 35],
        ['Хищный Плющ', 36],
    ];

    private const PERCENT_RESPAWN_MONSTER = 70;

    private const TIME_NOT_ATTACK = 15;

    /** Циклический паттерн count_monster по location_id % 5 — см. докблок класса */
    private const COUNT_MONSTER_CYCLE = [1, 2, 1, 2, 3];

    public function run(): void
    {
        $firstHalfIds = $this->monsterIds(self::FIRST_HALF_MONSTERS);
        $secondHalfIds = $this->monsterIds(self::SECOND_HALF_MONSTERS);

        $placedLocations = $this->placeMonsters(self::FIRST_HALF_RANGE, $firstHalfIds)
            + $this->placeMonsters(self::SECOND_HALF_RANGE, $secondHalfIds);

        $this->command?->info("OvergrownRoadMonsterPlacementSeeder: расставлено локаций — {$placedLocations}");
    }

    /** @param  array<int, array{0: string, 1: int}>  $pairs
     * @return array<int, int> monster_id по порядку $pairs */
    private function monsterIds(array $pairs): array
    {
        return array_map(
            fn (array $pair) => Monster::where('name', $pair[0])->where('lvl', $pair[1])->value('id')
                ?? throw new \RuntimeException("Монстр «{$pair[0]}» lvl {$pair[1]} не найден — прогоните OvergrownRoadMonsterSeeder сначала"),
            $pairs,
        );
    }

    /**
     * @param  array{0: int, 1: int}  $range  [minLocationId, maxLocationId]
     * @param  int[]  $monsterIds  пул видов для всего диапазона
     */
    private function placeMonsters(array $range, array $monsterIds): int
    {
        [$min, $max] = $range;

        // Идемпотентность: полностью пересобираем пул для этого диапазона локаций
        DB::table('location_has_monsters')
            ->whereBetween('location_id', [$min, $max])
            ->delete();

        $rows = [];
        for ($locationId = $min; $locationId <= $max; $locationId++) {
            foreach ($monsterIds as $monsterId) {
                $rows[] = [
                    'location_id' => $locationId,
                    'monster_id' => $monsterId,
                    'aggression' => null,
                ];
            }
        }

        DB::table('location_has_monsters')->insert($rows);

        DB::table('locations')
            ->whereBetween('id', [$min, $max])
            ->update([
                'percent_respawn_monster' => self::PERCENT_RESPAWN_MONSTER,
                'time_not_attack' => self::TIME_NOT_ATTACK,
            ]);

        // count_monster — по циклу, обновляется точечно на каждую локацию (DB::update не умеет per-row разное значение)
        for ($locationId = $min; $locationId <= $max; $locationId++) {
            $countMonster = self::COUNT_MONSTER_CYCLE[$locationId % count(self::COUNT_MONSTER_CYCLE)];
            DB::table('locations')->where('id', $locationId)->update(['count_monster' => $countMonster]);
        }

        return $max - $min + 1;
    }
}
