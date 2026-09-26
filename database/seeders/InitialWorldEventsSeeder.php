<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Domain\Services\WorldEventLifecycleService;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use RuntimeException;

final class InitialWorldEventsSeeder extends Seeder
{
    public function run(): void
    {
        $startsAt = now();

        $herbMapId = $this->mapId('Нейрин');
        $herbEvent = $this->createEvent(
            title: 'Целебные травы Нейрина',
            attributes: [
                'description' => '<p>В окрестностях городских ворот разросся целебный чертополох. Найдите растения и соберите их прежде, чем запас иссякнет.</p>',
                'objective_type' => WorldEventObjectiveType::COLLECT,
                'map_id' => $herbMapId,
                'influence_map_id' => $herbMapId,
                'share_item_id' => $this->itemId('Чертополох'),
                'monster_id' => null,
                'influence_name' => 'Нейрине',
                'next_start_at' => $startsAt,
                'repeat_interval_minutes' => 720,
                'duration_minutes' => 60,
                'global_limit' => 50,
                'player_limit' => 5,
                'spawn_limit' => 10,
                'respawn_seconds' => 300,
                'item_lifetime_minutes' => 1440,
                'influence_per_item' => 1,
                'is_active' => true,
            ],
            locationIds: [1, 24, 35, 100],
        );

        $monsterMapId = $this->mapId('Заброшеное кладбище');
        $monsterEvent = $this->createEvent(
            title: 'Нашествие пауков-трупоедов',
            attributes: [
                'description' => '<p>На Заброшенном кладбище пробудились могильные пауки-трупоеды. Уничтожьте тварей и укрепите своё влияние на этой территории.</p>',
                'objective_type' => WorldEventObjectiveType::KILL,
                'map_id' => $monsterMapId,
                'influence_map_id' => $monsterMapId,
                'share_item_id' => null,
                'monster_id' => $this->monsterId('Могильный Паук-Трупоед'),
                'influence_name' => 'Заброшенном кладбище',
                'next_start_at' => $startsAt,
                'repeat_interval_minutes' => 720,
                'duration_minutes' => 60,
                'global_limit' => 50,
                'player_limit' => 5,
                'spawn_limit' => 5,
                'respawn_seconds' => 120,
                'item_lifetime_minutes' => 1440,
                'influence_per_item' => 1,
                'is_active' => true,
            ],
            locationIds: [308, 319, 328, 342, 363],
        );

        $lifecycle = app(WorldEventLifecycleService::class);
        foreach ([$herbEvent, $monsterEvent] as $event) {
            if ($event->wasRecentlyCreated) {
                $lifecycle->start($event->id, $startsAt);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $locationIds
     */
    private function createEvent(string $title, array $attributes, array $locationIds): WorldEvent
    {
        $availableLocations = Location::query()
            ->whereIn('id', $locationIds)
            ->where('map_id', $attributes['map_id'])
            ->pluck('id');
        if ($availableLocations->count() !== count($locationIds)) {
            throw new RuntimeException("Не найдены все локации для события «{$title}».");
        }

        $event = WorldEvent::query()->firstOrCreate(['title' => $title], $attributes);
        $event->locations()->sync($availableLocations);
        $stage = $event->allStages()->firstOrCreate(
            ['position' => 1],
            [
                'title' => 'Этап 1',
                'objective_type' => $attributes['objective_type'],
                'share_item_id' => $attributes['share_item_id'],
                'monster_id' => $attributes['monster_id'],
                'global_limit' => $attributes['global_limit'],
                'player_limit' => $attributes['player_limit'],
                'spawn_limit' => $attributes['spawn_limit'],
                'respawn_seconds' => $attributes['respawn_seconds'],
                'item_lifetime_minutes' => $attributes['item_lifetime_minutes'],
                'influence_per_item' => $attributes['influence_per_item'],
                'is_active' => true,
            ],
        );
        $stage->allTargets()->firstOrCreate(
            [
                'share_item_id' => $attributes['share_item_id'],
                'monster_id' => $attributes['monster_id'],
            ],
            [
                'position' => 1,
                'spawn_weight' => 100,
                'max_active' => null,
                'is_active' => true,
            ],
        );

        return $event;
    }

    private function mapId(string $name): int
    {
        return (int) (Map::query()->where('name', $name)->value('id')
            ?? throw new RuntimeException("Карта «{$name}» не найдена."));
    }

    private function itemId(string $name): int
    {
        return (int) (ShareItem::query()->where('name', $name)->value('id')
            ?? throw new RuntimeException("Предмет «{$name}» не найден."));
    }

    private function monsterId(string $name): int
    {
        return (int) (Monster::query()->where('name', $name)->value('id')
            ?? throw new RuntimeException("Монстр «{$name}» не найден."));
    }
}
