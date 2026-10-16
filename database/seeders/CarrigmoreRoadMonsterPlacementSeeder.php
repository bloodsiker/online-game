<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет всех 6 монстров дороги на Карригмор (3 ступени Вепря +
 * 3 ступени Стервятника, см. CarrigmoreRoadMonsterSeeder) по всем 11
 * локациям (map_id=15, 1582-1592) — по прямому указанию пользователя:
 *   - в пуле на локацию все 6 ступеней сразу;
 *   - одновременно респаунится только 2 (locations.count_monster=2);
 *   - агрессия зафиксирована на 30% (location_has_monsters.aggression=30,
 *     переопределяет собственную агрессию видов — 90/60 у Вепря/Стервятника);
 *   - percent_respawn_monster=40, time_not_attack=15 сек.
 */
class CarrigmoreRoadMonsterPlacementSeeder extends Seeder
{
    private const MONSTER_NAMES = ['Вепрь', 'Стервятник'];

    private const ZONE_FROM = 1582;

    private const ZONE_TO = 1592;

    public function run(): void
    {
        $monsterIds = LocalizedContentSeederQuery::forTable('monsters')
            ->whereIn('name', self::MONSTER_NAMES)
            ->whereIn('lvl', [48, 51, 54, 49, 52, 55])
            ->pluck('id');

        if ($monsterIds->count() !== 6) {
            $this->command?->warn('ожидалось 6 monster-строк (3 Вепря + 3 Стервятника), найдено: '.$monsterIds->count());
        }

        $locationIds = DB::table('locations')
            ->whereBetween('id', [self::ZONE_FROM, self::ZONE_TO])
            ->orderBy('id')
            ->pluck('id');

        // Идемпотентность: убираем свои же старые строки перед повторной расстановкой.
        DB::table('location_has_monsters')
            ->whereIn('location_id', $locationIds)
            ->whereIn('monster_id', $monsterIds)
            ->delete();

        $rows = [];
        foreach ($locationIds as $locationId) {
            foreach ($monsterIds as $monsterId) {
                $rows[] = ['location_id' => $locationId, 'monster_id' => $monsterId, 'aggression' => 30];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('location_has_monsters')->insert($chunk);
        }

        DB::table('locations')
            ->whereIn('id', $locationIds)
            ->update([
                'count_monster' => 2,
                'percent_respawn_monster' => 40,
                'time_not_attack' => 15,
                'updated_at' => now(),
            ]);

        $this->command?->info(sprintf(
            'CarrigmoreRoadMonsterPlacementSeeder: %d локаций, %d строк расстановки (6 видов/ступеней в пуле, респаунится максимум 2)',
            $locationIds->count(), count($rows),
        ));
    }
}
