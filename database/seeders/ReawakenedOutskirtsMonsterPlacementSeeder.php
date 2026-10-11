<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Заменяет расстановку монстров на «Ожившая окраина» (map 4) — по
 * прямому указанию пользователя: пепельная троица (AshFoothillsMonsterSeeder,
 * id 317-322) должна жить ТОЛЬКО в «Пепельных Предгорьях» (map 16), а на
 * map 4 — два других вида (ReawakenedOutskirtsMonsterSeeder, id 323-328).
 *
 * Сносит старые строки location_has_monsters для id 317-322 на
 * локациях map 4 (на map 16 их не трогает), затем расставляет 323-328.
 * Параметры расстановки не менялись: оба вида на каждой локации, тир
 * выбирается случайно независимо для каждого вида, count_monster=3,
 * percent_respawn_monster=70, time_not_attack=20, aggression override=80.
 */
class ReawakenedOutskirtsMonsterPlacementSeeder extends Seeder
{
    private const LOCATION_AGGRESSION_OVERRIDE = 80;

    private const ASH_TRIO_IDS = [317, 318, 319, 320, 321, 322];

    /** @var array<string, array<int,int>> вид => id тиров */
    private const SPECIES_TIER_IDS = [
        'Терновый Волк' => [323, 324, 325],
        'Степной Шакал' => [326, 327, 328],
    ];

    private const EXCLUDE_LOCATION_IDS = [245];

    public function run(): void
    {
        $locationIds = DB::table('locations')
            ->where('map_id', 4)
            ->whereNotIn('id', self::EXCLUDE_LOCATION_IDS)
            ->orderBy('id')
            ->pluck('id');

        DB::table('location_has_monsters')
            ->whereIn('location_id', $locationIds)
            ->whereIn('monster_id', self::ASH_TRIO_IDS)
            ->delete();

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

        $this->command?->info(sprintf(
            'ReawakenedOutskirtsMonsterPlacementSeeder: пепельная троица снята с %d локаций map4, расставлено %d строк (2 вида на каждой)',
            $locationIds->count(), count($rows),
        ));
    }
}
