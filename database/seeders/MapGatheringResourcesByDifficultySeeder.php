<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Включает добычу ресурсов (map_gathering_resources + maps.has_gathering_field)
 * для всех карт, кроме Нейрина (столица — по прямому указанию пользователя,
 * не дикая зона), по образцу уже настроенного «Шепчущий Лес» (map_id=2).
 *
 * Тир (rarity) ресурса растёт вместе со сложностью карты (диапазоном
 * уровней монстров на ней) — по прямому указанию пользователя. Каталог
 * ресурсов (plant/fish/precious_gem/wood, 5 тиров × 3 предмета) не имеет
 * явных level-requirements (share_item_requirements пуст для всех 60
 * позиций) — тир подобран вручную под уровень карты:
 *
 *   common     (как у «Шепчущий Лес», lvl4-20): Канализация (lvl1-6, ТОЛЬКО рыба)
 *   uncommon   (lvl6-36): Опустевшая Долина, Забытый Курган, Заросшая дорога
 *   rare       (lvl35-45): Ожившая окраина, Пепельные Предгорья, Сторожевые Холмы
 *   epic       (lvl40-60): Заброшеное кладбище, Катакомбы Жертвоприношений, Гранитный Перевал
 *   legendary  (lvl48-60): Карригмор, Пепельные Глубины (полный набор),
 *                          Заваленная Штольня (ТОЛЬКО самоцветы — шахта, pickaxe)
 *
 * Канализация и Заваленная Штольня — по прямому указанию пользователя
 * получают только тематически уместный тип ресурса (рыба в стоках /
 * самоцветы в шахте), а не весь набор из 4 семейств.
 *
 * Координаты/лимиты точек — те же общие границы и типичный max_active,
 * что и у уже настроенного «Шепчущий Лес» (min/max x/y = 8..92).
 */
class MapGatheringResourcesByDifficultySeeder extends Seeder
{
    private const BOUNDS = ['min_x' => 8, 'max_x' => 92, 'min_y' => 8, 'max_y' => 92];

    private const DEFAULT_MAX_ACTIVE = 2;

    /**
     * map_id => list of share_item_id (любой набор — не обязательно все 4 семейства).
     *
     * @var array<int, array<int, int>>
     */
    private const MAP_RESOURCES = [
        // Канализация (lvl1-6) — только рыба, common.
        7 => [368], // Ивовый голавль

        // uncommon (lvl6-36)
        3 => [377, 382, 388, 384],  // Опустевшая Долина: Розмарин, Пятнистая щука, Сапфир, Кедровое бревно
        8 => [378, 381, 387, 383],  // Забытый Курган: Ирис, Жемчужный судак, Обсидиан, Ясеневое бревно
        9 => [379, 380, 386, 385],  // Заросшая дорога: Тысячелистник, Серебристый лосось, Аметист, Тисовое бревно

        // rare (lvl35-45)
        4 => [469, 475, 423, 481],  // Ожившая окраина: Белладонна, Осётр, Рубин, Чёрное бревно
        16 => [470, 476, 424, 482], // Пепельные Предгорья: Мандрагора, Сом-исполин, Изумруд, Палисандровое бревно
        10 => [471, 477, 425, 483], // Сторожевые Холмы: Лунный цветок, Электрический угорь, Топаз, Бревно секвойи

        // epic (lvl40-60)
        5 => [472, 478, 426, 484],  // Заброшеное кладбище: Нарцисс, Жемчужная белуга, Алмаз, Бревно драконьего дерева
        6 => [473, 479, 427, 485],  // Катакомбы Жертвоприношений: Анемон, Огненный лосось, Александрит, Бревно огненного клёна
        13 => [474, 480, 428, 486], // Гранитный Перевал: Лотос, Ледяной таймень, Чёрный опал, Бревно ледяной ели

        // legendary (lvl48-60)
        15 => [490, 493, 487, 496], // Карригмор: Корень бессмертия, Левиафан, Звёздный камень, Бревно небесное
        17 => [491, 494, 488, 497], // Пепельные Глубины: Вечноцвет, Роковая, Сердце горы, Бревно древа вечности
        14 => [489],                // Заваленная Штольня: только Застывшая молния (самоцвет, шахта)
    ];

    public function run(): void
    {
        if (DB::table('map_gathering_resources')->where('map_id', 7)->exists()) {
            $this->command?->info('MapGatheringResourcesByDifficultySeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();
        $rowsCreated = 0;

        foreach (self::MAP_RESOURCES as $mapId => $shareItemIds) {
            foreach ($shareItemIds as $shareItemId) {
                DB::table('map_gathering_resources')->insert([
                    'map_id' => $mapId,
                    'share_item_id' => $shareItemId,
                    'max_active' => self::DEFAULT_MAX_ACTIVE,
                    'min_x' => self::BOUNDS['min_x'],
                    'max_x' => self::BOUNDS['max_x'],
                    'min_y' => self::BOUNDS['min_y'],
                    'max_y' => self::BOUNDS['max_y'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $rowsCreated++;
            }

            DB::table('maps')->where('id', $mapId)->update([
                'has_gathering_field' => 1,
                'updated_at' => $now,
            ]);
        }

        $this->command?->info(sprintf(
            'MapGatheringResourcesByDifficultySeeder: %d карт включено, %d строк добавлено',
            count(self::MAP_RESOURCES), $rowsCreated,
        ));
    }
}
