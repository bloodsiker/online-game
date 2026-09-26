<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

final class CarrigmoreLocationsSeeder extends Seeder
{
    private const FIRST_LOCATION_ID = 1582;

    private const LAST_LOCATION_ID = 1629;

    private const ENTRANCE_LOCATION_ID = 1581;

    private const PARENT_MAP_FOLDER = 'carrigmore/zavalennaya_stolnya';

    private const MAP_FOLDER = 'carrigmore/main';

    private const MAP_SLUG = 'Fd19lMn67Dsq';

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
                throw new LogicException('Для Карригмора не найдена родительская карта Заваленная Штольня.');
            }

            $parentMapId = (int) $parentMap->id;
            $entrance = DB::table('locations')
                ->where('id', self::ENTRANCE_LOCATION_ID)
                ->first(['id', 'map_id', 'west']);

            if ($entrance === null || (int) $entrance->map_id !== $parentMapId) {
                throw new LogicException('Для Карригмора не найден вход из Заваленной Штольни (location_id=1581).');
            }

            if ($entrance->west !== null && (int) $entrance->west !== self::FIRST_LOCATION_ID) {
                throw new LogicException('Западный переход локации 1581 уже занят другой локацией.');
            }

            $slugOwner = DB::table('maps')
                ->where('slug', self::MAP_SLUG)
                ->where('folder', '!=', self::MAP_FOLDER)
                ->exists();

            if ($slugOwner) {
                throw new LogicException('Slug Карригмора уже используется другой картой.');
            }

            $map = DB::table('maps')->where('folder', self::MAP_FOLDER)->first();

            if ($map === null) {
                $mapId = DB::table('maps')->insertGetId([
                    'parent_id' => $parentMapId,
                    'name' => 'Карригмор',
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
                    'name' => 'Карригмор',
                    'slug' => self::MAP_SLUG,
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
                $content = $this->locationContent($cell);

                if (isset($existingIds[$locationId])) {
                    DB::table('locations')
                        ->where('id', $locationId)
                        ->where('name', '')
                        ->update(['name' => $content['name']]);
                    DB::table('locations')
                        ->where('id', $locationId)
                        ->where(function ($query): void {
                            $query->whereNull('description')->orWhere('description', '');
                        })
                        ->update(['description' => $content['description']]);

                    continue;
                }

                $newLocations[] = [
                    'id' => $locationId,
                    'map_id' => $mapId,
                    'dungeon_id' => null,
                    'name' => $content['name'],
                    'description' => $content['description'],
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
                'west' => self::FIRST_LOCATION_ID,
                'updated_at' => $now,
            ]);

            DB::table('maps')->where('id', $mapId)->update([
                'resp_location_id' => self::FIRST_LOCATION_ID,
                'updated_at' => $now,
            ]);

            return count($newLocations);
        });

        $this->command?->info(sprintf(
            'Карта «Карригмор»: создано %d локаций, переходы синхронизированы.',
            $createdLocations,
        ));
    }

    /**
     * @return array<string, array{id: int, row: int, column: int, walls: array<string, true>, area: string}>
     */
    private function cells(): array
    {
        $mapData = require resource_path('data/maps/carrigmore.php');
        /** @var array<int, array<int, array{int, string, 2?: string}>> $layout */
        $layout = $mapData['cells'];
        $defaultArea = (string) $mapData['default_area'];
        $cells = [];
        $ids = [];

        foreach ($layout as $row => $columns) {
            foreach ($columns as $column => $cell) {
                $locationId = $cell[0];
                $borderClasses = $cell[1];
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
                    'area' => (string) ($cell[2] ?? $defaultArea),
                ];
            }
        }

        $expectedIds = range(self::FIRST_LOCATION_ID, self::LAST_LOCATION_ID);
        $actualIds = array_keys($ids);
        sort($actualIds);

        if ($actualIds !== $expectedIds) {
            throw new LogicException('Схема Карригмора должна содержать все локации 1582–1629 без пропусков.');
        }

        return $cells;
    }

    /**
     * @param  array<string, array{id: int, row: int, column: int, walls: array<string, true>, area: string}>  $cells
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

        $moves[self::FIRST_LOCATION_ID]['east'] = self::ENTRANCE_LOCATION_ID;

        return $moves;
    }

    /**
     * @param  array{id: int, row: int, column: int, walls: array<string, true>, area: string}  $cell
     * @return array{name: string, description: string}
     */
    private function locationContent(array $cell): array
    {
        $isNorthGallery = $cell['area'] === 'north_galleries';

        return [
            'name' => match (true) {
                $cell['id'] === self::FIRST_LOCATION_ID => 'Вход в Карригмор',
                $isNorthGallery => sprintf('Северные галереи — участок %d', $cell['id']),
                default => sprintf('Карригмор — участок %d', $cell['id']),
            },
            'description' => $isNorthGallery
                ? 'Северные галереи Карригмора тянутся среди холодных каменных стен и старых переходов.'
                : 'Дорога проходит через каменные переходы и древние участки Карригмора.',
        ];
    }

    private function cellKey(int $row, int $column): string
    {
        return sprintf('%d:%d', $row, $column);
    }
}
