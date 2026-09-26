<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Dungeon\Domain\Enums\DungeonCooldownType;
use App\Modules\Dungeon\Domain\Enums\DungeonRewardType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageCompletionType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TowerDungeonSeeder extends Seeder
{
    private const ENTRY_LOCATION_ID = 6;

    private const FLOOR_COUNT = 3;

    private const CELLS_PER_FLOOR = 9;

    private const MONSTERS_PER_FLOOR = 30;

    private const FLOOR_TIME_SECONDS = 180;

    public function run(): void
    {
        if (Dungeon::query()->where('name', 'Башня испытаний')->exists()) {
            $this->command?->info('TowerDungeonSeeder: «Башня испытаний» уже существует.');

            return;
        }

        DB::transaction(function (): void {
            $dungeon = Dungeon::query()->create([
                'name' => 'Башня испытаний',
                'description' => 'Поднимайтесь этаж за этажом. На зачистку каждого этажа даётся три минуты.',
                'tier' => 1,
                'type' => DungeonType::TOWER,
                'max_players' => 1,
                'cooldown_type' => DungeonCooldownType::PERSONAL,
                'cooldown_seconds' => 3600,
                'time_limit_seconds' => self::FLOOR_COUNT * self::FLOOR_TIME_SECONDS,
                'min_level' => 1,
                'is_active' => true,
                'entry_location_id' => self::ENTRY_LOCATION_ID,
                'return_location_id' => self::ENTRY_LOCATION_ID,
                'monster_respawn' => false,
                'xp_multiplier' => 1.25,
            ]);

            $stages = [];
            $firstLocationId = null;
            $lastLocationId = null;
            $monsterIds = [1, 2, 33];

            for ($floor = 1; $floor <= self::FLOOR_COUNT; $floor++) {
                $locations = $this->createFloorLocations($dungeon, $floor);
                $firstLocationId ??= $locations[0]->id;
                $lastLocationId = $locations[4]->id;

                $stage = DungeonStage::query()->create([
                    'dungeon_id' => $dungeon->id,
                    'number' => $floor,
                    'name' => "Этаж {$floor}",
                    'time_limit_seconds' => self::FLOOR_TIME_SECONDS,
                    'completion_type' => DungeonStageCompletionType::KILL_ALL,
                    'spawn_type' => DungeonStageSpawnType::DISTRIBUTED,
                    'total_monsters' => self::MONSTERS_PER_FLOOR,
                ]);

                foreach ($locations as $position => $location) {
                    $stage->locations()->attach($location->id, [
                        'is_entry' => $position === 0,
                        'position' => $position,
                    ]);
                }
                $stage->monsters()->create([
                    'monster_id' => $monsterIds[$floor - 1],
                    'quantity' => self::MONSTERS_PER_FLOOR,
                    'weight' => 1,
                ]);
                $stages[] = $stage;
            }

            foreach ($stages as $index => $stage) {
                $stage->update(['next_stage_id' => $stages[$index + 1]->id ?? null]);
            }

            $dungeon->update([
                'first_location_id' => $firstLocationId,
                'exit_location_id' => $lastLocationId,
            ]);

            $dungeon->rewards()->createMany([
                ['type' => DungeonRewardType::GOLD, 'amount_min' => 500, 'amount_max' => 800, 'drop_chance' => 100],
                ['type' => DungeonRewardType::EXPERIENCE, 'amount_min' => 500, 'amount_max' => 500, 'drop_chance' => 100],
            ]);
        });

        $this->command?->info('TowerDungeonSeeder: создана «Башня испытаний» — 3 этажа × 9 клеток × 30 монстров.');
    }

    /** @return list<Location> */
    private function createFloorLocations(Dungeon $dungeon, int $floor): array
    {
        $locations = [];
        for ($cell = 1; $cell <= self::CELLS_PER_FLOOR; $cell++) {
            $locations[] = Location::query()->create([
                'name' => "Башня испытаний — этаж {$floor}, клетка {$cell}",
                'description' => "Каменная площадка {$cell} на {$floor}-м этаже башни.",
                'dungeon_id' => $dungeon->id,
                'time_not_attack' => 1,
                'percent_respawn_monster' => 0,
                'count_monster' => 0,
            ]);
        }

        foreach ($locations as $index => $location) {
            $row = intdiv($index, 3);
            $column = $index % 3;
            $location->update([
                'north' => $row > 0 ? $locations[$index - 3]->id : null,
                'south' => $row < 2 ? $locations[$index + 3]->id : null,
                'west' => $column > 0 ? $locations[$index - 1]->id : null,
                'east' => $column < 2 ? $locations[$index + 1]->id : null,
            ]);
        }

        return $locations;
    }
}
