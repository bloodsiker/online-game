<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

final class WatchHillsSeeder extends Seeder
{
    private const FIRST_LOCATION_ID = 1116;

    private const LAST_LOCATION_ID = 1278;

    private const OMITTED_LOCATION_ID = 1127;

    private const ENTRANCE_LOCATION_ID = 1115;

    private const PARENT_MAP_FOLDER = 'subcity/overgrown_road';

    private const MAP_FOLDER = 'subcity/watch_hills';

    private const MAP_SLUG = 'jKg6d4Tr0K';

    public function run(): void
    {
        $cells = $this->cells();
        $moves = $this->moves($cells);

        $createdLocations = DB::transaction(function () use ($cells, $moves): int {
            $now = now();
            $parentMap = DB::table('maps')
                ->where('folder', self::PARENT_MAP_FOLDER)
                ->first(['id']);

            if ($parentMap === null) {
                throw new LogicException('Для Сторожевых Холмов не найдена родительская карта Заросшая дорога.');
            }

            $parentMapId = (int) $parentMap->id;
            $entrance = DB::table('locations')
                ->where('id', self::ENTRANCE_LOCATION_ID)
                ->first(['id', 'map_id', 'south']);

            if ($entrance === null || (int) $entrance->map_id !== $parentMapId) {
                throw new LogicException('Для Сторожевых Холмов не найден вход из Заросшей дороги (location_id=1115).');
            }

            if ($entrance->south !== null && (int) $entrance->south !== self::FIRST_LOCATION_ID) {
                throw new LogicException('Южный переход локации 1115 уже занят другой локацией.');
            }

            $slugOwner = DB::table('maps')
                ->where('slug', self::MAP_SLUG)
                ->where('folder', '!=', self::MAP_FOLDER)
                ->exists();

            if ($slugOwner) {
                throw new LogicException('Slug Сторожевых Холмов уже используется другой картой.');
            }

            $map = DB::table('maps')->where('folder', self::MAP_FOLDER)->first();

            if ($map === null) {
                $mapId = DB::table('maps')->insertGetId([
                    'parent_id' => $parentMapId,
                    'name' => 'Сторожевые Холмы',
                    'folder' => self::MAP_FOLDER,
                    'slug' => self::MAP_SLUG,
                    'resp_location_id' => 6,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $mapId = (int) $map->id;

                DB::table('maps')->where('id', $mapId)->update([
                    'parent_id' => $parentMapId,
                    'name' => 'Сторожевые Холмы',
                    'slug' => self::MAP_SLUG,
                    'resp_location_id' => 6,
                    'updated_at' => $now,
                ]);
            }

            $locationIds = array_column($cells, 'id');
            $foreignLocation = DB::table('locations')
                ->whereIn('id', $locationIds)
                ->where(function ($query) use ($mapId): void {
                    $query->whereNull('map_id')->orWhere('map_id', '!=', $mapId);
                })
                ->first(['id', 'map_id']);

            if ($foreignLocation !== null) {
                throw new LogicException(sprintf(
                    'Локация %d уже принадлежит другой карте (map_id=%d).',
                    $foreignLocation->id,
                    $foreignLocation->map_id,
                ));
            }

            $existingIds = DB::table('locations')
                ->whereIn('id', $locationIds)
                ->pluck('id')
                ->mapWithKeys(fn ($id): array => [(int) $id => true])
                ->all();
            $newLocations = [];

            foreach ($cells as $cell) {
                $locationId = $cell['id'];

                if (isset($existingIds[$locationId])) {
                    continue;
                }

                $newLocations[] = [
                    'id' => $locationId,
                    'map_id' => $mapId,
                    'dungeon_id' => null,
                    'name' => $locationId === self::FIRST_LOCATION_ID
                        ? 'Вход в Сторожевые Холмы'
                        : sprintf('Сторожевые Холмы — участок %d', $locationId),
                    'description' => $locationId === self::FIRST_LOCATION_ID
                        ? 'Заросшая дорога остаётся позади. Впереди поднимаются продуваемые ветрами холмы и старые сторожевые тропы.'
                        : 'Тропа петляет среди поросших травой холмов, каменных выступов и остатков старых сторожевых укреплений.',
                    'image' => null,
                    'north' => null,
                    'south' => null,
                    'east' => null,
                    'west' => null,
                    'up' => null,
                    'down' => null,
                    'count_monster' => 0,
                    'percent_respawn_monster' => 0,
                    'time_not_attack' => 0,
                    'is_locked' => 0,
                    'last_respawn_monster_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($newLocations, 100) as $chunk) {
                DB::table('locations')->insert($chunk);
            }

            foreach ($moves as $locationId => $locationMoves) {
                DB::table('locations')->where('id', $locationId)->update([
                    ...$locationMoves,
                    'map_id' => $mapId,
                    'updated_at' => $now,
                ]);
            }

            DB::table('locations')->where('id', self::ENTRANCE_LOCATION_ID)->update([
                'south' => self::FIRST_LOCATION_ID,
                'updated_at' => $now,
            ]);

            return count($newLocations);
        });

        $this->command?->info(sprintf(
            'Карта «Сторожевые Холмы»: создано %d локаций, переходы синхронизированы.',
            $createdLocations,
        ));
    }

    /**
     * @return array<string, array{id: int, row: int, column: int, walls: array<string, true>}>
     */
    private function cells(): array
    {
        /** @var array<int, array<int, array{int, string}>> $layout */
        $layout = require resource_path('data/maps/watch_hills.php');
        $cells = [];
        $ids = [];

        foreach ($layout as $row => $columns) {
            foreach ($columns as $column => [$locationId, $borderClasses]) {
                $key = $this->cellKey($row, $column);
                $walls = trim($borderClasses) === ''
                    ? []
                    : array_fill_keys(preg_split('/\s+/', trim($borderClasses)), true);

                if (isset($ids[$locationId])) {
                    throw new LogicException(sprintf('Локация %d повторяется в схеме карты.', $locationId));
                }

                $ids[$locationId] = true;
                $cells[$key] = [
                    'id' => $locationId,
                    'row' => $row,
                    'column' => $column,
                    'walls' => $walls,
                ];
            }
        }

        $expectedIds = array_values(array_diff(
            range(self::FIRST_LOCATION_ID, self::LAST_LOCATION_ID),
            [self::OMITTED_LOCATION_ID],
        ));
        $actualIds = array_keys($ids);
        sort($actualIds);

        if ($actualIds !== $expectedIds) {
            throw new LogicException('Схема Сторожевых Холмов должна содержать локации 1116–1278, кроме 1127.');
        }

        return $cells;
    }

    /**
     * @param  array<string, array{id: int, row: int, column: int, walls: array<string, true>}>  $cells
     * @return array<int, array{north: ?int, south: ?int, east: ?int, west: ?int, up: null, down: null}>
     */
    private function moves(array $cells): array
    {
        $directions = [
            'north' => [-1, 0, 'bt', 'bb'],
            'south' => [1, 0, 'bb', 'bt'],
            'east' => [0, 1, 'br', 'bl'],
            'west' => [0, -1, 'bl', 'br'],
        ];
        $moves = [];

        foreach ($cells as $cell) {
            $locationMoves = [
                'north' => null,
                'south' => null,
                'east' => null,
                'west' => null,
                'up' => null,
                'down' => null,
            ];

            foreach ($directions as $direction => [$rowDelta, $columnDelta, $wall, $oppositeWall]) {
                $neighbour = $cells[$this->cellKey(
                    $cell['row'] + $rowDelta,
                    $cell['column'] + $columnDelta,
                )] ?? null;

                if ($neighbour === null) {
                    continue;
                }

                $hasWall = isset($cell['walls'][$wall]);
                $neighbourHasWall = isset($neighbour['walls'][$oppositeWall]);

                if ($hasWall !== $neighbourHasWall) {
                    throw new LogicException(sprintf(
                        'Несовпадающая стена между локациями %d и %d.',
                        $cell['id'],
                        $neighbour['id'],
                    ));
                }

                if (! $hasWall) {
                    $locationMoves[$direction] = $neighbour['id'];
                }
            }

            $moves[$cell['id']] = $locationMoves;
        }

        $moves[self::FIRST_LOCATION_ID]['north'] = self::ENTRANCE_LOCATION_ID;

        return $moves;
    }

    private function cellKey(int $row, int $column): string
    {
        return sprintf('%d:%d', $row, $column);
    }
}
