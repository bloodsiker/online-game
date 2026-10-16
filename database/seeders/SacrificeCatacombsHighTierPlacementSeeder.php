<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет монстров уровня 50-60 (старшая ступень каждого вида, см.
 * SacrificeCatacombsMonsterSeeder) по ОСТАЛЬНЫМ локациям «Катакомб
 * Жертвоприношений» (map_id=6, 511-818) — по прямому указанию
 * пользователя: те же параметры, что и у младшей зоны
 * (SacrificeCatacombsLowTierPlacementSeeder) — пул 5 из 6 видов
 * случайно без повтора вида на локации, count_monster=3,
 * percent_respawn_monster=70, аггрессия override=80, time_not_attack=20.
 *
 * Зона — все локации карты ЗА ВЫЧЕТОМ трёх уже заселённых младшей
 * ступенью (511-581, 660-682, 743-818).
 */
class SacrificeCatacombsHighTierPlacementSeeder extends Seeder
{
    private const MAP_ID = 6;

    private const LOW_TIER_ZONE_RANGES = [[511, 581], [660, 682], [743, 818]];

    private const POOL_SIZE_PER_LOCATION = 5;

    private const LOCATION_AGGRESSION_OVERRIDE = 80;

    /** @var array<int,string> */
    private const SPECIES_NAMES = [
        'Кровавый Гончий',
        'Кровавый Оракул',
        'Кровавый Палач',
        'Кровопийца-Инквизитор',
        'Пожиратель Грехов',
        'Тень Приговорённого',
    ];

    public function run(): void
    {
        // Для каждого вида — старшие ступени (>=50).
        $monsterIdsBySpecies = [];
        foreach (self::SPECIES_NAMES as $name) {
            $rows = LocalizedContentSeederQuery::forTable('monsters')->where('name', $name)->where('lvl', '>=', 50)->orderBy('lvl')->pluck('id', 'lvl');
            $monsterIdsBySpecies[$name] = $rows->values()->all();
        }

        $excludedLocationIds = collect();
        foreach (self::LOW_TIER_ZONE_RANGES as [$from, $to]) {
            $excludedLocationIds = $excludedLocationIds->merge(
                DB::table('locations')->whereBetween('id', [$from, $to])->pluck('id')
            );
        }

        $locationIds = DB::table('locations')
            ->where('map_id', self::MAP_ID)
            ->whereNotIn('id', $excludedLocationIds)
            ->orderBy('id')
            ->pluck('id');

        $allMonsterIds = collect($monsterIdsBySpecies)->flatten()->all();

        DB::table('location_has_monsters')
            ->whereIn('location_id', $locationIds)
            ->whereIn('monster_id', $allMonsterIds)
            ->delete();

        $rows = [];
        foreach ($locationIds as $locationId) {
            $chosenSpecies = (array) array_rand(array_flip(self::SPECIES_NAMES), self::POOL_SIZE_PER_LOCATION);

            foreach ($chosenSpecies as $speciesName) {
                $levels = $monsterIdsBySpecies[$speciesName];
                $monsterId = $levels[array_rand($levels)];

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
            'SacrificeCatacombsHighTierPlacementSeeder: %d локаций, %d строк расстановки (5 случайных видов из 6 на локацию)',
            $locationIds->count(), count($rows),
        ));
    }
}
