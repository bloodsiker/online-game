<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Расставляет монстров «Сторожевых Холмов» (map_id=10, локации 1116-1278) в
 * пул респавна `location_has_monsters` — по прямому указанию пользователя,
 * 3 полосы по глубине зоны, вход (1116, «Вход в Сторожевые Холмы») без
 * монстров, локация 1127 отсутствует в БД (пропускается сама собой).
 *
 * Правило пользователя: если уровень вида в полосе не указан явно — ставятся
 * ОБА уровня этого вида (не только младший); если указан явно (один
 * конкретный уровень) — ставится только он.
 *
 * Полоса 1 (1117-1164, 47 локаций) — 4 стартовых вида, оба уровня каждого:
 *   Земляной страж (35+38), Ветряной страж (36+39), Костяной Часовой (37+40),
 *   Дух Павшего Знаменосца (35+39).
 * Полоса 2 (1165-1231, 67 локаций) — Костяной Часовой/Дух Павшего ТОЛЬКО на
 *   старшем уровне (40/39, явно указано) + 2 новых вида, оба уровня каждого:
 *   Стальной Страж (38+42), Пепельный Страж (39+43).
 * Полоса 3 (1232-1278, 47 локаций) — Стальной Страж/Пепельный Страж ТОЛЬКО на
 *   старшем уровне (42/43, явно указано) + 2 новых вида, оба уровня каждого:
 *   Теневой Страж (40+44), Туманный Страж (41+45).
 *
 * Все 8 видов (16 monster-строк из WatchHillsMonsterSeeder +
 * WatchHillsAdditionalMonsterSeeder) задействованы полностью — ни один
 * уровень не остаётся без места.
 *
 * Каждая локация полосы получает по одной строке на каждую monster-запись
 * списка (aggression=null, как в GranitePassMonsterPlacementSeeder).
 */
class WatchHillsMonsterPlacementSeeder extends Seeder
{
    /** @var array<int, array{range: array{0:int,1:int}, monsters: array<int, array{string,int}>}> */
    private const BANDS = [
        [
            'range' => [1117, 1164],
            'monsters' => [
                ['Земляной страж', 35], ['Земляной страж', 38],
                ['Ветряной страж', 36], ['Ветряной страж', 39],
                ['Костяной Часовой', 37], ['Костяной Часовой', 40],
                ['Дух Павшего Знаменосца', 35], ['Дух Павшего Знаменосца', 39],
            ],
        ],
        [
            'range' => [1165, 1231],
            'monsters' => [
                ['Костяной Часовой', 40],
                ['Дух Павшего Знаменосца', 39],
                ['Стальной Страж', 38], ['Стальной Страж', 42],
                ['Пепельный Страж', 39], ['Пепельный Страж', 43],
            ],
        ],
        [
            'range' => [1232, 1278],
            'monsters' => [
                ['Стальной Страж', 42],
                ['Пепельный Страж', 43],
                ['Теневой Страж', 40], ['Теневой Страж', 44],
                ['Туманный Страж', 41], ['Туманный Страж', 45],
            ],
        ],
    ];

    public function run(): void
    {
        $inserted = 0;

        foreach (self::BANDS as $band) {
            [$fromId, $toId] = $band['range'];

            $monsterIds = [];
            foreach ($band['monsters'] as [$name, $lvl]) {
                $monster = DB::table('monsters')->where('name', $name)->where('lvl', $lvl)->first();
                if ($monster === null) {
                    $this->command?->warn("не найден монстр «{$name}» lvl {$lvl}");

                    continue;
                }
                $monsterIds[] = $monster->id;
            }

            $locationIds = DB::table('locations')
                ->whereBetween('id', [$fromId, $toId])
                ->pluck('id');

            $rows = [];
            foreach ($locationIds as $locationId) {
                foreach ($monsterIds as $monsterId) {
                    $exists = DB::table('location_has_monsters')
                        ->where('location_id', $locationId)
                        ->where('monster_id', $monsterId)
                        ->exists();
                    if ($exists) {
                        continue;
                    }
                    $rows[] = [
                        'location_id' => $locationId,
                        'monster_id' => $monsterId,
                        'aggression' => null,
                    ];
                }
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('location_has_monsters')->insert($chunk);
                $inserted += count($chunk);
            }

            $this->command?->info(sprintf(
                'полоса %d-%d: %d локаций × %d видов = %d строк',
                $fromId, $toId, $locationIds->count(), count($monsterIds), count($rows),
            ));
        }

        $this->command?->info("WatchHillsMonsterPlacementSeeder: вставлено строк — {$inserted}");
    }
}
