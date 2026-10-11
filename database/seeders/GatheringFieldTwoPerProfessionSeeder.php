<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Уточнение пользователя: «+1 ресурс» было мало — нужно РОВНО 2 ресурса
 * КАЖДОЙ профессии (plant/skill6, fish/skill7, gem/skill8, wood/skill9)
 * на каждой карте, где эта профессия вообще представлена.
 *
 * Канализация и Заваленная Штольня НЕ трогаются — у них по прямому
 * указанию пользователя всего 1 профессия (рыба / самоцветы
 * соответственно, см. MapGatheringResourcesByDifficultySeeder), и обе
 * уже имеют по 2 предмета этой профессии после GatheringFieldVarietyBoostSeeder
 * (добавление доп. рыбы/камня) — условие «2 на профессию» для них уже
 * выполнено, расширять до остальных 3 семейств пользователь не просил.
 *
 * До этого сидера расклад был неровным (GatheringFieldVarietyBoostSeeder
 * добавлял только по 1 растению): plant=2, но fish=1/gem=1/wood=1 на
 * всех «полных» картах, кроме «Шепчущий Лес» (там наоборот — fish=2,
 * gem=2, но plant=1, wood=1). Этот сидер добирает недостающее до 2/2/2/2
 * — используя тот же паттерн ротации внутри тройки карт одного тира
 * (каждой карте — предмет СОСЕДНЕЙ карты той же профессии/тира), что и
 * предыдущий сидер для растений, т.к. все 3 уникальных предмета тира на
 * семью уже разобраны по 1 штуке.
 */
class GatheringFieldTwoPerProfessionSeeder extends Seeder
{
    private const BOUNDS = ['min_x' => 8, 'max_x' => 92, 'min_y' => 8, 'max_y' => 92];

    private const DEFAULT_MAX_ACTIVE = 2;

    /** map_id => list of share_item_id для добавления. */
    private const ADDITIONS = [
        // Шепчущий Лес (common): не хватало 2-го растения и 2-го дерева.
        2 => [371, 375], // Чертополох (plant), Сосновое бревно (wood)

        // uncommon — ротация fish/gem/wood внутри тройки (3→8→9→3)
        3 => [381, 387, 383],  // Опустевшая Долина += Жемчужный судак, Обсидиан, Ясеневое бревно
        8 => [380, 386, 385],  // Забытый Курган += Серебристый лосось, Аметист, Тисовое бревно
        9 => [382, 388, 384],  // Заросшая дорога += Пятнистая щука, Сапфир, Кедровое бревно

        // rare — ротация (4→16→10→4)
        4 => [476, 424, 482],  // Ожившая окраина += Сом-исполин, Изумруд, Палисандровое бревно
        16 => [477, 425, 483], // Пепельные Предгорья += Электрический угорь, Топаз, Бревно секвойи
        10 => [475, 423, 481], // Сторожевые Холмы += Осётр, Рубин, Чёрное бревно

        // epic — ротация (5→6→13→5)
        5 => [479, 427, 485],  // Заброшеное кладбище += Огненный лосось, Александрит, Бревно огненного клёна
        6 => [480, 428, 486],  // Катакомбы Жертвоприношений += Ледяной таймень, Чёрный опал, Бревно ледяной ели
        13 => [478, 426, 484], // Гранитный Перевал += Жемчужная белуга, Алмаз, Бревно драконьего дерева

        // legendary — только 2 карты (Штольня отдельно, gem-only), используем оставшиеся 3-и предметы семей
        15 => [494, 488, 497], // Карригмор += Роковая, Сердце горы, Бревно древа вечности
        17 => [495, 489, 498], // Пепельные Глубины += Первородная, Застывшая молния, Бревно истока
    ];

    public function run(): void
    {
        if (DB::table('map_gathering_resources')->where('map_id', 2)->where('share_item_id', 371)->exists()) {
            $this->command?->info('GatheringFieldTwoPerProfessionSeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();
        $rowsCreated = 0;

        foreach (self::ADDITIONS as $mapId => $shareItemIds) {
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
        }

        $this->command?->info("GatheringFieldTwoPerProfessionSeeder: добавлено {$rowsCreated} строк (2/2/2/2 по профессиям на всех полных картах)");
    }
}
