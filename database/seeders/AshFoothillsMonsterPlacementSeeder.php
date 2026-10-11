<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет 3 вида «Пепельных Предгорий» (см. AshFoothillsMonsterSeeder,
 * id 317-322) по «Ожившая окраина» (map 4) и «Пепельные Предгорья»
 * (map 16) — по прямому указанию пользователя (диапазон 35-40 для обеих
 * карт).
 *
 * Входные/транзитные локации исключены из расстановки (без монстров,
 * как и у остальных "входов" на карте): 245 «Лестница Жертвоприношений»
 * (спуск в катакомбы), 1630 «Вход в Пепельные Предгорья».
 *
 * Параметры расстановки — по умолчанию, т.к. пользователь не задавал их
 * явно в этот раз (взяты по аналогии с последней похожей зоной —
 * Катакомбы Жертвоприношений): все 3 вида на каждой локации, тир
 * (lvl35/40) выбирается случайно независимо для каждого вида,
 * count_monster=3, percent_respawn_monster=70, time_not_attack=20,
 * аггрессия override=80.
 */
class AshFoothillsMonsterPlacementSeeder extends Seeder
{
    private const LOCATION_AGGRESSION_OVERRIDE = 80;

    /** @var array<string, array{0:int,1:int}> вид => [id тира lvl35, id тира lvl40] */
    private const SPECIES_TIER_IDS = [
        'Пепельный Скорпион' => [317, 318],
        'Пепельная Саламандра' => [319, 320],
        'Выжженная Гиена' => [321, 322],
    ];

    private const EXCLUDE_LOCATION_IDS = [245, 1630];

    public function run(): void
    {
        $locationIds = DB::table('locations')
            ->whereIn('map_id', [4, 16])
            ->whereNotIn('id', self::EXCLUDE_LOCATION_IDS)
            ->orderBy('id')
            ->pluck('id');

        $allMonsterIds = collect(self::SPECIES_TIER_IDS)->flatten()->all();

        DB::table('location_has_monsters')
            ->whereIn('location_id', $locationIds)
            ->whereIn('monster_id', $allMonsterIds)
            ->delete();

        $rows = [];
        foreach ($locationIds as $locationId) {
            foreach (self::SPECIES_TIER_IDS as $tierIds) {
                $monsterId = $tierIds[array_rand($tierIds)];

                $rows[] = [
                    'location_id' => $locationId,
                    'monster_id' => $monsterId,
                    'aggression' => self::LOCATION_AGGRESSION_OVERRIDE,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('location_has_monsters')->insert($chunk);
        }

        DB::table('locations')
            ->whereIn('id', $locationIds)
            ->update([
                'count_monster' => 3,
                'percent_respawn_monster' => 70,
                'time_not_attack' => 20,
                'updated_at' => now(),
            ]);

        $this->command?->info(sprintf(
            'AshFoothillsMonsterPlacementSeeder: %d локаций, %d строк расстановки (3 вида на каждой)',
            $locationIds->count(), count($rows),
        ));
    }
}
