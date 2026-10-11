<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * По прямому указанию пользователя:
 *   1. «Шепчущий Лес» (map_id=2) не имел дерева вообще (2 рыбы + 1 трава +
 *      1 камень, 0 дерева) — добавляет common-тир дерево.
 *   2. На ВСЕ карты с gathering (все 14 после п.1) добавляет ещё по 1
 *      ресурсу СВОЕГО тира (не меняя уже расставленные — только +1).
 *
 * Канализация/Заваленная Штольня остаются тематически ограничены (рыба
 * /самоцветы — см. MapGatheringResourcesByDifficultySeeder), «+1» для
 * них тоже той же темы. Остальные карты — доп. предмет семейства
 * «растение», подобранный ротацией внутри своей тройки карт одного
 * тира (3 растения на тир у 3 карт => каждая карта получает растение
 * соседней карты по тому же тиру), т.к. все 3 уникальных растения тира
 * уже разобраны по 1 на карту — переиспользование между картами
 * ожидаемо и ничем не ограничено.
 */
class GatheringFieldVarietyBoostSeeder extends Seeder
{
    private const BOUNDS = ['min_x' => 8, 'max_x' => 92, 'min_y' => 8, 'max_y' => 92];

    private const DEFAULT_MAX_ACTIVE = 2;

    /** map_id => share_item_id для добавления. */
    private const ADDITIONS = [
        2 => 374,   // Шепчущий Лес: Дубовое бревно (wood, common) — явный запрос пользователя
        7 => 367,   // Канализация: Родниковая форель (fish, common)
        14 => 488,  // Заваленная Штольня: Сердце горы (precious_gem, legendary)
        3 => 378,   // Опустевшая Долина: Ирис (plant, uncommon)
        8 => 379,   // Забытый Курган: Тысячелистник (plant, uncommon)
        9 => 377,   // Заросшая дорога: Розмарин (plant, uncommon)
        4 => 470,   // Ожившая окраина: Мандрагора (plant, rare)
        16 => 471,  // Пепельные Предгорья: Лунный цветок (plant, rare)
        10 => 469,  // Сторожевые Холмы: Белладонна (plant, rare)
        5 => 473,   // Заброшеное кладбище: Анемон (plant, epic)
        6 => 474,   // Катакомбы Жертвоприношений: Лотос (plant, epic)
        13 => 472,  // Гранитный Перевал: Нарцисс (plant, epic)
        15 => 491,  // Карригмор: Вечноцвет (plant, legendary)
        17 => 492,  // Пепельные Глубины: Пустоцвет (plant, legendary)
    ];

    /** Шепчущий Лес дополнительно получает ещё и камень (common gem) — своя вторая строка сверх дерева. */
    private const EXTRA_FOR_FOREST = 370; // Бирюза (precious_gem, common)

    public function run(): void
    {
        if (DB::table('map_gathering_resources')->where('map_id', 2)->where('share_item_id', 374)->exists()) {
            $this->command?->info('GatheringFieldVarietyBoostSeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();
        $rowsCreated = 0;

        foreach (self::ADDITIONS as $mapId => $shareItemId) {
            $this->addResource($mapId, $shareItemId, $now);
            $rowsCreated++;
        }

        $this->addResource(2, self::EXTRA_FOR_FOREST, $now);
        $rowsCreated++;

        $this->command?->info("GatheringFieldVarietyBoostSeeder: добавлено {$rowsCreated} строк (дерево в Шепчущий Лес + по 1 ресурсу на все 14 карт)");
    }

    private function addResource(int $mapId, int $shareItemId, $now): void
    {
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
    }
}
