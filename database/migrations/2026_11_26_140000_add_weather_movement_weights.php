<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Метель, попутный ветер и ураган по картам (этап 2). @var array<int, array<string, int>> */
    private const WEIGHTS = [
        2 => ['tailwind' => 1],                    // Шепчущий Лес
        3 => ['tailwind' => 2, 'hurricane' => 1],  // Опустевшая Долина
        4 => ['hurricane' => 1],                   // Ожившая окраина
        9 => ['tailwind' => 2],                    // Заросшая дорога
        10 => ['tailwind' => 2, 'hurricane' => 2], // Сторожевые Холмы
        13 => ['blizzard' => 4, 'hurricane' => 2], // Гранитный Перевал
        16 => ['tailwind' => 1, 'hurricane' => 1], // Пепельные Предгорья
    ];

    public function up(): void
    {
        $now = now();
        $maps = DB::table('maps')->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        foreach (self::WEIGHTS as $mapId => $weights) {
            if (! in_array($mapId, $maps, true)) {
                continue;
            }
            foreach ($weights as $weather => $weight) {
                DB::table('map_weather_weights')->updateOrInsert(
                    ['map_id' => $mapId, 'weather' => $weather],
                    ['weight' => $weight, 'created_at' => $now, 'updated_at' => $now],
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('map_weather_weights')->whereIn('weather', ['blizzard', 'tailwind', 'hurricane'])->delete();
    }
};
