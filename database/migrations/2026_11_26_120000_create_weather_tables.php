<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Стартовые веса явлений по картам (города и подземелья без погоды).
     * Пока выпадают только гроза и туман (WeatherRules::ACTIVE_TYPES); радуга приходит после грозы.
     *
     * @var array<int, array<string, int>>
     */
    private const WEIGHTS = [
        2 => ['storm' => 3, 'fog' => 3],   // Шепчущий Лес
        3 => ['storm' => 3, 'fog' => 2],   // Опустевшая Долина
        4 => ['storm' => 3, 'fog' => 2],   // Ожившая окраина
        5 => ['storm' => 2, 'fog' => 5],   // Заброшенное кладбище
        8 => ['storm' => 2, 'fog' => 4],   // Забытый Курган
        9 => ['storm' => 3, 'fog' => 2],   // Заросшая дорога
        10 => ['storm' => 3, 'fog' => 2],  // Сторожевые Холмы
        13 => ['storm' => 2, 'fog' => 2],  // Гранитный Перевал
        16 => ['storm' => 2, 'fog' => 1],  // Пепельные Предгорья
    ];

    private const STATS = ['strength', 'agility', 'intuition', 'wisdom', 'intelligence', 'endurance'];

    public function up(): void
    {
        Schema::create('map_weather', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->unique()->constrained('maps')->cascadeOnDelete();
            $table->string('weather', 32)->nullable();
            $table->string('previous_weather', 32)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at');
            $table->timestamps();
        });

        Schema::create('map_weather_weights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->string('weather', 32);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->timestamps();
            $table->unique(['map_id', 'weather']);
        });

        $now = now();
        $existingMaps = DB::table('maps')->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        foreach (self::WEIGHTS as $mapId => $weights) {
            if (! in_array($mapId, $existingMaps, true)) {
                continue;
            }
            foreach ($weights as $weather => $weight) {
                DB::table('map_weather_weights')->insert([
                    'map_id' => $mapId, 'weather' => $weather, 'weight' => $weight, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $effects = [
            ['slug' => 'weather_cold', 'type' => 'debuff', 'percent' => -10,
                'name' => ['ru' => 'Простуда', 'uk' => 'Застуда'],
                'description' => ['ru' => 'Вы промокли под грозой и простудились: характеристики снижены.', 'uk' => 'Ви промокли під грозою та застудилися: характеристики знижено.']],
            ['slug' => 'weather_rainbow', 'type' => 'buff', 'percent' => 5,
                'name' => ['ru' => 'Радуга', 'uk' => 'Веселка'],
                'description' => ['ru' => 'Радуга после грозы придаёт сил: характеристики повышены.', 'uk' => 'Веселка після грози додає сил: характеристики підвищено.']],
        ];
        foreach ($effects as $effect) {
            if (DB::table('effects')->where('slug', $effect['slug'])->exists()) {
                continue;
            }
            $row = [
                'slug' => $effect['slug'],
                'type' => $effect['type'],
                'duration' => 15 * 60,
                'stat_modifiers' => json_encode(array_map(
                    static fn (string $stat): array => ['type' => $stat, 'value' => $effect['percent'], 'is_percent' => true],
                    self::STATS,
                ), JSON_UNESCAPED_UNICODE),
                'is_dispellable' => true,
                'name' => json_encode($effect['name'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($effect['description'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            foreach (['name_legacy' => $effect['name']['ru'], 'description_legacy' => $effect['description']['ru']] as $column => $value) {
                if (Schema::hasColumn('effects', $column)) {
                    $row[$column] = $value;
                }
            }
            DB::table('effects')->insert($row);
        }
    }

    public function down(): void
    {
        DB::table('effects')->whereIn('slug', ['weather_cold', 'weather_rainbow'])->delete();
        Schema::dropIfExists('map_weather_weights');
        Schema::dropIfExists('map_weather');
    }
};
