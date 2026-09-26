<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Influence\Domain\Enums\InfluenceBonusType;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceMedal;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;
use App\Modules\Location\Infrastructure\Persistence\Models\Map as GameMap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class InfluenceProgressionSeeder extends Seeder
{
    public function run(): void
    {
        $influenceMapIds = DB::table('world_events')
            ->selectRaw('COALESCE(influence_map_id, map_id) AS influence_map_id')
            ->distinct()
            ->pluck('influence_map_id');
        $maps = GameMap::query()->whereIn('id', $influenceMapIds)->get();

        foreach ($maps as $map) {
            if (MapInfluenceLevel::query()->where('map_id', $map->id)->exists()) {
                continue;
            }

            $levels = [
                [1, 'Знакомый', 1_000, 'Знак знакомства', 5, 0, 0, 0],
                [2, 'Уважаемый', 5_000, 'Медаль уважения', 10, 2, 1, 1],
                [3, 'Союзник', 15_000, 'Знак союзника', 25, 3, 1.5, 2],
                [4, 'Защитник', 50_000, 'Медаль защитника', 50, 4, 2, 3],
                [5, 'Герой территории', 150_000, 'Герб героя', 100, 5, 3, 5],
            ];

            foreach ($levels as [$number, $levelName, $threshold, $medalName, $rating, $speed, $resource, $money]) {
                $medal = InfluenceMedal::query()->create([
                    'map_id' => $map->id,
                    'name' => $medalName.' — '.$map->name,
                    'description' => sprintf('Награда за достижение уровня влияния «%s» на территории «%s».', $levelName, $map->name),
                    'rating_points' => $rating,
                ]);
                $level = MapInfluenceLevel::query()->create([
                    'map_id' => $map->id,
                    'level' => $number,
                    'name' => $levelName,
                    'required_influence' => $threshold,
                    'influence_medal_id' => $medal->id,
                    'is_active' => true,
                    'sort_order' => $number,
                ]);

                foreach ([
                    InfluenceBonusType::GATHERING_SPEED_PERCENT->value => $speed,
                    InfluenceBonusType::BONUS_RESOURCE_CHANCE_PERCENT->value => $resource,
                    InfluenceBonusType::MONSTER_MONEY_PERCENT->value => $money,
                ] as $type => $value) {
                    $level->bonuses()->create(['bonus_type' => $type, 'value' => $value]);
                }
            }
        }
    }
}
