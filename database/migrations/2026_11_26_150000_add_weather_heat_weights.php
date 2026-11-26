<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Зной по картам (этап 3). @var array<int, int> */
    private const WEIGHTS = [
        3 => 2,  // Опустевшая Долина
        4 => 1,  // Ожившая окраина
        10 => 1, // Сторожевые Холмы
        16 => 3, // Пепельные Предгорья
    ];

    public function up(): void
    {
        $now = now();
        $maps = DB::table('maps')->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        foreach (self::WEIGHTS as $mapId => $weight) {
            if (in_array($mapId, $maps, true)) {
                DB::table('map_weather_weights')->updateOrInsert(
                    ['map_id' => $mapId, 'weather' => 'heat'],
                    ['weight' => $weight, 'created_at' => $now, 'updated_at' => $now],
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('map_weather_weights')->where('weather', 'heat')->delete();
    }
};
