<?php

declare(strict_types=1);

namespace App\Modules\ItemSpawn\Application\Services;

use App\Modules\Item\Domain\Enums\LocationItemInteractionType;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Weather\Application\Services\WeatherService;
use App\Modules\Weather\Domain\Enums\WeatherType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Периодическое появление предметов на локациях карты (грибы и т.п.). Предметы кладутся на землю как обычная
 * добыча «Искать здесь» (без профессии), живут ttl_minutes и затем удаляются командой items:delete-expired-location.
 * Во время дождя (гроза, радуга после неё) за тик появляется больше предметов, но общий лимит карты не превышается.
 */
final class MapItemSpawner
{
    public function __construct(private readonly WeatherService $weather) {}

    /** @return int сколько предметов появилось */
    public function tick(): int
    {
        $spawned = 0;
        $rules = DB::table('map_item_spawn_rules')->where('is_active', true)->get();
        foreach ($rules as $rule) {
            if ($rule->last_run_at !== null && now()->diffInMinutes($rule->last_run_at, true) < (int) $rule->interval_minutes) {
                continue;
            }
            $spawned += DB::transaction(fn (): int => $this->runRule($rule));
        }

        return $spawned;
    }

    private function runRule(object $rule): int
    {
        DB::table('map_item_spawn_rules')->where('id', $rule->id)->update(['last_run_at' => now()]);

        $items = DB::table('map_item_spawn_rule_items')->where('rule_id', $rule->id)->where('weight', '>', 0)->get();
        if ($items->isEmpty()) {
            return 0;
        }

        $locationIds = DB::table('locations')
            ->where('map_id', $rule->map_id)
            ->whereNull('dungeon_id')
            ->where('is_locked', false)
            ->pluck('id')
            ->all();
        if ($locationIds === []) {
            return 0;
        }

        $present = (int) DB::table('item_on_locations as iol')
            ->join('items as i', 'i.id', '=', 'iol.item_id')
            ->join('locations as l', 'l.id', '=', 'iol.location_id')
            ->where('l.map_id', $rule->map_id)
            ->whereIn('i.share_item_id', $items->pluck('share_item_id'))
            ->where(fn ($q) => $q->whereNull('iol.expires_at')->orWhere('iol.expires_at', '>', now()))
            ->sum('iol.count');

        $perTick = (int) $rule->per_tick;
        $weather = $this->weather->currentForMap((int) $rule->map_id)?->weather;
        if (in_array($weather, [WeatherType::STORM, WeatherType::RAINBOW], true)) {
            $perTick = (int) ceil($perTick * (float) $rule->rain_multiplier);
        }
        $toSpawn = min($perTick, max(0, (int) $rule->map_max - $present));

        $created = 0;
        for ($i = 0; $i < $toSpawn; $i++) {
            $row = $this->pickWeighted($items);
            $count = random_int((int) $row->min_count, max((int) $row->min_count, (int) $row->max_count));
            $item = Item::query()->create(['share_item_id' => (int) $row->share_item_id]);

            $slot = new ItemOnLocation;
            $slot->item_id = $item->id;
            $slot->location_id = $locationIds[array_rand($locationIds)];
            $slot->count = $count;
            $slot->interaction_type = LocationItemInteractionType::PICKUP;
            $slot->expires_at = now()->addMinutes((int) $rule->ttl_minutes);
            $slot->save();
            $created += $count;
        }

        return $created;
    }

    /** @param  Collection<int, object>  $items */
    private function pickWeighted($items): object
    {
        $roll = random_int(1, max(1, (int) $items->sum('weight')));
        foreach ($items as $item) {
            $roll -= (int) $item->weight;
            if ($roll <= 0) {
                return $item;
            }
        }

        return $items->last();
    }
}
