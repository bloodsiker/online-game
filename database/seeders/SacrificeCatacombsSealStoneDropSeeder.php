<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Привязывает дроп 4 «Камней Печати» (id 29 Синий/30 Зелёный/31 Красный/
 * 883 Фиолетовый — уже существовали в базе с лором катакомб
 * жертвоприношений, но без единого источника дропа) ко всем 24 монстрам
 * SacrificeCatacombsMonsterSeeder — по прямому указанию пользователя.
 *
 * drop_chance — ОДИН фиксированный % на цвет камня, одинаковый во всей
 * карте (обеих зонах): Зелёный 60%, Синий 45%, Фиолетовый 30%, Красный 15%.
 *
 * min_count/max_count — различаются по зоне (сколько камней выпадает за
 * срабатывание дропа):
 *   Зона 1 (SacrificeCatacombsLowTierPlacementSeeder, уровни 40-50):
 *     Зелёный 10-15, Синий 7-10, Фиолетовый 4-7, Красный 1-4.
 *   Зона 2 (SacrificeCatacombsHighTierPlacementSeeder, уровни 50-60):
 *     Зелёный 12-17, Синий 9-12, Фиолетовый 6-9, Красный 3-6.
 *
 * Монстр lvl50 (Кровавый Гончий, Тень Приговорённого) состоит в ОБЕИХ
 * зонах сразу (пограничный уровень, см. оба placement-сидера) — получает
 * count зоны 1, чтобы не заводить для одного monster_id два
 * конфликтующих min/max_count на один и тот же share_item_id.
 */
class SacrificeCatacombsSealStoneDropSeeder extends Seeder
{
    private const STONE_ITEM_IDS = [
        'green' => 30,
        'blue' => 29,
        'purple' => 883,
        'red' => 31,
    ];

    /** @var array<string, int> */
    private const DROP_CHANCE = [
        'green' => 60,
        'blue' => 45,
        'purple' => 30,
        'red' => 15,
    ];

    /** @var array<string, array{0:int,1:int}> */
    private const ZONE_1_COUNTS = [
        'green' => [10, 15],
        'blue' => [7, 10],
        'purple' => [4, 7],
        'red' => [1, 4],
    ];

    /** @var array<string, array{0:int,1:int}> */
    private const ZONE_2_COUNTS = [
        'green' => [12, 17],
        'blue' => [9, 12],
        'purple' => [6, 9],
        'red' => [3, 6],
    ];

    private const ZONE_1_LOCATION_RANGES = [[511, 581], [660, 682], [743, 818]];

    public function run(): void
    {
        $zone1LocationIds = collect();
        foreach (self::ZONE_1_LOCATION_RANGES as [$from, $to]) {
            $zone1LocationIds = $zone1LocationIds->merge(
                DB::table('locations')->whereBetween('id', [$from, $to])->pluck('id')
            );
        }

        $zone1MonsterIds = DB::table('location_has_monsters')
            ->whereIn('location_id', $zone1LocationIds)
            ->distinct()
            ->pluck('monster_id');

        $zone2LocationIds = DB::table('locations')
            ->where('map_id', 6)
            ->whereNotIn('id', $zone1LocationIds)
            ->pluck('id');

        $zone2MonsterIds = DB::table('location_has_monsters')
            ->whereIn('location_id', $zone2LocationIds)
            ->distinct()
            ->pluck('monster_id');

        // lvl50 встречается в обеих зонах — count по ставке зоны 1 (см. докблок).
        $zone2OnlyMonsterIds = $zone2MonsterIds->diff($zone1MonsterIds);

        // Сносим то, что успел проставить предыдущий (ошибочный) прогон —
        // там drop_chance был диапазоном, а не фиксированным %.
        DB::table('monster_has_items')
            ->whereIn('share_item_id', self::STONE_ITEM_IDS)
            ->whereIn('monster_id', $zone1MonsterIds->merge($zone2MonsterIds)->unique())
            ->delete();

        $this->seedZone($zone1MonsterIds, self::ZONE_1_COUNTS);
        $this->seedZone($zone2OnlyMonsterIds, self::ZONE_2_COUNTS);

        $this->command?->info('SacrificeCatacombsSealStoneDropSeeder: дроп камней печати привязан (фикс. % + диапазон количества по зоне)');
    }

    /** @param  array<string, array{0:int,1:int}>  $counts */
    private function seedZone(\Illuminate\Support\Collection $monsterIds, array $counts): void
    {
        foreach ($monsterIds as $monsterId) {
            foreach (self::STONE_ITEM_IDS as $color => $itemId) {
                [$minCount, $maxCount] = $counts[$color];

                DB::table('monster_has_items')->insert([
                    'monster_id' => $monsterId,
                    'share_item_id' => $itemId,
                    'drop_chance' => self::DROP_CHANCE[$color],
                    'min_count' => $minCount,
                    'max_count' => $maxCount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
