<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Регистрирует 2 новые карты по прямому указанию пользователя —
 * продолжение цепочки «Опустевшая Долина»(3) → «Ожившая окраина»(4),
 * которая до этого упиралась в тупиковый кластер локаций 387-395
 * (location 395 «Среди могил» — без south/west выхода):
 *
 *   «Пепельные Предгорья» (parent_id=4, подъём от 395) →
 *   «Пепельные Глубины» (parent_id=<Предгорья>, пещеры).
 *
 * Полная сетка локаций/монстров — следующим шагом, отдельно (по
 * прямому указанию пользователя — "локации позже будут"). Здесь только
 * сами карты + по одной стартовой location-заглушке на каждую (нужна
 * как обязательный resp_location_id — FK NOT NULL на locations), по
 * конвенции остальных карт («Вход в <Имя карты>» + короткая связка с
 * предыдущей зоной, см. location 1530/1279/1116). Заглушка «Предгорья»
 * подключена к 395 (единственный свободный exit — south), заглушка
 * «Глубины» пока ничем не связана — соединение появится при постройке
 * полной сетки «Предгорья».
 */
class AshFoothillsAndDepthsMapSeeder extends Seeder
{
    private const PARENT_MAP_ID = 4; // Ожившая окраина

    private const CONNECTOR_LOCATION_ID = 395; // Среди могил (тупик, south свободен)

    public function run(): void
    {
        if (DB::table('maps')->where('name', 'Пепельные Предгорья')->exists()) {
            $this->command?->info('AshFoothillsAndDepthsMapSeeder: карты уже существуют, пропускаю.');

            return;
        }

        $foothillsLocationId = DB::table('locations')->insertGetId([
            'map_id' => null,
            'name' => 'Вход в Пепельные Предгорья',
            'description' => 'Ожившая окраина остаётся позади — тропа уходит вверх, и под ногами всё чаще хрустит мелкий пепел вместо земли. Склоны вокруг окрашены в блёклый серо-чёрный цвет, будто здесь когда-то прошёл низовой пожар, выжегший всё дотла.',
            'north' => self::CONNECTOR_LOCATION_ID,
            'count_monster' => 0,
            'percent_respawn_monster' => 0,
            'time_not_attack' => 0,
            'is_locked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $foothillsMapId = DB::table('maps')->insertGetId([
            'parent_id' => self::PARENT_MAP_ID,
            'name' => 'Пепельные Предгорья',
            'folder' => 'subcity/ash_foothills',
            'slug' => Str::random(10),
            'resp_location_id' => $foothillsLocationId,
            'has_gathering_field' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('locations')->where('id', $foothillsLocationId)->update(['map_id' => $foothillsMapId, 'updated_at' => now()]);
        DB::table('locations')->where('id', self::CONNECTOR_LOCATION_ID)->update(['south' => $foothillsLocationId, 'updated_at' => now()]);

        $depthsLocationId = DB::table('locations')->insertGetId([
            'map_id' => null,
            'name' => 'Вход в Пепельные Глубины',
            'description' => 'Пепельные Предгорья остаются позади — в склоне открывается широкий провал, из которого тянет сухим жаром и застарелой гарью. Дальше тропа уходит под землю.',
            'count_monster' => 0,
            'percent_respawn_monster' => 0,
            'time_not_attack' => 0,
            'is_locked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $depthsMapId = DB::table('maps')->insertGetId([
            'parent_id' => $foothillsMapId,
            'name' => 'Пепельные Глубины',
            'folder' => 'subcity/ash_depths',
            'slug' => Str::random(10),
            'resp_location_id' => $depthsLocationId,
            'has_gathering_field' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('locations')->where('id', $depthsLocationId)->update(['map_id' => $depthsMapId, 'updated_at' => now()]);

        $this->command?->info(sprintf(
            'AshFoothillsAndDepthsMapSeeder: карта «Пепельные Предгорья» id=%d (location id=%d), карта «Пепельные Глубины» id=%d (location id=%d)',
            $foothillsMapId, $foothillsLocationId, $depthsMapId, $depthsLocationId,
        ));
    }
}
