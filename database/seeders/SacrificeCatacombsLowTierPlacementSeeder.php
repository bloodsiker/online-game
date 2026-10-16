<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет монстров уровня 40-50 (младшая из двух ступеней каждого
 * вида, см. SacrificeCatacombsMonsterSeeder) по трём зонам «Катакомб
 * Жертвоприношений» (map_id=6) — по прямому указанию пользователя:
 *   - зоны: 511-581, 660-682, 743-818;
 *   - пул на локацию — 5 ИЗ 6 видов (случайно, без повтора вида на
 *     локации — как и в AbandonedCemeteryMonsterSeeder), для каждого
 *     выбранного вида случайно берётся один из его двух уровней ≤50;
 *   - одновременно респаунится до 3 (count_monster=3), 70% шанс
 *     респауна, агрессия override 80%, 20 сек до повторной атаки.
 *
 * Локации вне этих трёх зон (в диапазоне карты, но не перечисленные) —
 * отдельная задача под уровни 50-60, ждём параметры респавна от
 * пользователя отдельным сообщением.
 */
class SacrificeCatacombsLowTierPlacementSeeder extends Seeder
{
    private const ZONE_RANGES = [[511, 581], [660, 682], [743, 818]];

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
        // Для каждого вида — только 2 младшие ступени (<=50).
        $monsterIdsBySpecies = [];
        foreach (self::SPECIES_NAMES as $name) {
            $rows = LocalizedContentSeederQuery::forTable('monsters')->where('name', $name)->where('lvl', '<=', 50)->orderBy('lvl')->pluck('id', 'lvl');
            $monsterIdsBySpecies[$name] = $rows->values()->all();
        }

        $locationIds = collect();
        foreach (self::ZONE_RANGES as [$from, $to]) {
            $locationIds = $locationIds->merge(
                DB::table('locations')->whereBetween('id', [$from, $to])->orderBy('id')->pluck('id')
            );
        }

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
            'SacrificeCatacombsLowTierPlacementSeeder: %d локаций, %d строк расстановки (5 случайных видов из 6 на локацию)',
            $locationIds->count(), count($rows),
        ));
    }
}
