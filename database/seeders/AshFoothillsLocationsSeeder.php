<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

final class AshFoothillsLocationsSeeder extends Seeder
{
    public function topology(): array
    {
        $data = require resource_path('data/maps/ash_foothills.php');
        $cells = $data['cells'];
        $moves = [];
        $directions = [
            'north' => [-1, 0, 'bt', 'bb'],
            'south' => [1, 0, 'bb', 'bt'],
            'east' => [0, 1, 'br', 'bl'],
            'west' => [0, -1, 'bl', 'br'],
        ];
        foreach ($cells as $row => $columns) {
            foreach ($columns as $column => [$id, $walls]) {
                if (isset($moves[$id])) {
                    throw new LogicException('Повторяющийся ID в схеме Предгорий.');
                }
                $moves[$id] = [];
                foreach ($directions as $direction => [$dr, $dc, $wall, $oppositeWall]) {
                    $neighbor = $cells[$row + $dr][$column + $dc] ?? null;
                    $moves[$id][$direction] = $neighbor !== null
                        && ! in_array($wall, explode(' ', $walls), true)
                        && ! in_array($oppositeWall, explode(' ', $neighbor[1]), true)
                        ? $neighbor[0] : null;
                }
            }
        }
        ksort($moves);
        if (array_keys($moves) !== range(1630, 1699)) {
            throw new LogicException('В схеме должны быть все локации 1630–1699.');
        }
        $moves[1630]['east'] = 395;

        return $moves;
    }

    public function run(): void
    {
        $moves = $this->topology();
        $created = DB::transaction(function () use ($moves): int {
            $map = DB::table('maps')->where('folder', 'subcity/ash_foothills')->lockForUpdate()->first();
            if ($map === null) {
                throw new LogicException('Не найдена карта Пепельные Предгорья.');
            }
            $existing = DB::table('locations')->whereIn('id', array_keys($moves))->lockForUpdate()->get()->keyBy('id');
            $connector = DB::table('locations')->where('id', 395)->lockForUpdate()->first();
            if (! $existing->has(1630) || $connector === null
                || (int) $existing[1630]->east !== 395 || (int) $connector->south !== 1630) {
                throw new LogicException('Существующий переход 1630 → 395 и 395 → 1630 не соответствует ожидаемому.');
            }
            foreach ($existing as $id => $location) {
                if ((int) $location->map_id !== (int) $map->id) {
                    throw new LogicException("ID {$id} занят локацией другой карты.");
                }
                foreach ($moves[$id] as $direction => $target) {
                    if ($location->{$direction} !== null && (int) $location->{$direction} !== $target) {
                        throw new LogicException("У локации {$id} уже занят переход {$direction}. Изменения отменены.");
                    }
                }
            }

            $new = [];
            foreach ($moves as $id => $directions) {
                if (! $existing->has($id)) {
                    $new[] = [
                        'id' => $id,
                        'map_id' => $map->id,
                        'name' => "Пепельные Предгорья — участок {$id}",
                        'description' => 'Пепельная тропа петляет среди выгоревших склонов и обломков скал. Сухой ветер поднимает над землёй серую пыль.',
                        'count_monster' => 0,
                        'percent_respawn_monster' => 0,
                        'time_not_attack' => 0,
                        'is_locked' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($new !== []) {
                DB::table('locations')->insert($new);
            }
            foreach ($moves as $id => $directions) {
                // Existing content, images, vertical exits and map settings are preserved.
                DB::table('locations')->where('id', $id)->update([...$directions, 'updated_at' => now()]);
            }

            return count($new);
        });
        $this->command?->info("Пепельные Предгорья: создано {$created} локаций; переходы 1630–1699 установлены по схеме.");
    }
}
