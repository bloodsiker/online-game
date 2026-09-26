<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Services;

use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Domain\Events\WorldEventStarted;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventStageTarget;
use App\Modules\Item\Domain\Enums\LocationItemInteractionType;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Share\Domain\Enums\ShareItemType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class WorldEventLifecycleService
{
    /** @return array{started: int, finished: int, spawned: int} */
    public function tick(Carbon $now): array
    {
        $stats = ['started' => 0, 'finished' => 0, 'spawned' => 0];

        WorldEventRun::query()
            ->where('status', WorldEventRun::STATUS_COMPLETED)
            ->pluck('id')
            ->each(fn (int $runId) => $this->cleanupTargets($runId));

        WorldEventRun::query()
            ->where('status', WorldEventRun::STATUS_ACTIVE)
            ->where('ends_at', '<=', $now)
            ->pluck('id')
            ->each(function (int $runId) use (&$stats, $now): void {
                $this->finish($runId, WorldEventRun::STATUS_EXPIRED, $now);
                $stats['finished']++;
            });

        WorldEvent::query()
            ->where('is_active', true)
            ->whereNotNull('next_start_at')
            ->where('next_start_at', '<=', $now)
            ->pluck('id')
            ->each(function (int $eventId) use (&$stats, $now): void {
                if ($this->start($eventId, $now) !== null) {
                    $stats['started']++;
                }
            });

        WorldEventRun::query()
            ->where('status', WorldEventRun::STATUS_ACTIVE)
            ->where(fn ($query) => $query->whereNull('next_spawn_at')->orWhere('next_spawn_at', '<=', $now))
            ->pluck('id')
            ->each(function (int $runId) use (&$stats, $now): void {
                $stats['spawned'] += $this->replenish($runId, $now);
            });

        return $stats;
    }

    public function start(int $eventId, ?Carbon $now = null): ?WorldEventRun
    {
        $now ??= now();

        $run = DB::transaction(function () use ($eventId, $now): ?WorldEventRun {
            $event = WorldEvent::query()->with(['locations', 'stages'])->lockForUpdate()->findOrFail($eventId);

            $alreadyActive = WorldEventRun::query()
                ->where('world_event_id', $event->id)
                ->where('status', WorldEventRun::STATUS_ACTIVE)
                ->exists();
            $hasLocations = $event->locations->isNotEmpty()
                || Location::query()->where('map_id', $event->map_id)->exists();
            $firstStage = $event->stages->first();
            if ($alreadyActive || ! $hasLocations || $firstStage === null) {
                return null;
            }

            $run = WorldEventRun::query()->create([
                'world_event_id' => $event->id,
                'current_stage_id' => $firstStage->id,
                'status' => WorldEventRun::STATUS_ACTIVE,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addMinutes((int) $event->duration_minutes),
            ]);

            // Следующий запуск назначается только после фактического завершения.
            $event->next_start_at = null;
            $event->save();

            return $run;
        });

        if ($run !== null) {
            $this->replenish($run->id, $now);
            $run->loadMissing('event.map');
            WorldEventStarted::dispatch(
                eventId: (int) $run->world_event_id,
                runId: (int) $run->id,
                title: $run->event->title,
                mapName: $run->event->map?->name ?? 'неизвестная карта',
                mapSlug: $run->event->map?->slug,
            );
        }

        return $run;
    }

    public function finish(int $runId, string $status = WorldEventRun::STATUS_CANCELLED, ?Carbon $now = null): void
    {
        $now ??= now();

        DB::transaction(function () use ($runId, $status, $now): void {
            $run = WorldEventRun::query()->lockForUpdate()->find($runId);
            if ($run === null || $run->status !== WorldEventRun::STATUS_ACTIVE) {
                return;
            }

            $finishedAt = $status === WorldEventRun::STATUS_EXPIRED && $run->ends_at->lte($now)
                ? $run->ends_at->copy()
                : $now;
            $event = WorldEvent::query()->lockForUpdate()->findOrFail($run->world_event_id);
            $event->scheduleNextStartAfter($finishedAt);
            $event->save();

            $run->update(['status' => $status, 'finished_at' => $finishedAt, 'next_spawn_at' => null]);
            $this->cleanupTargets($run->id);
        });
    }

    public function deleteExpiredInventoryItems(Carbon $now): int
    {
        $items = Item::query()->whereNotNull('expires_at')->where('expires_at', '<=', $now)->get(['id']);
        $count = $items->count();

        if ($count === 0) {
            return 0;
        }

        DB::transaction(function () use ($items): void {
            $itemIds = $items->pluck('id');

            // Единственная старая связь без cascadeOnDelete. Предмет должен
            // исчезнуть по таймеру независимо от того, где он сейчас хранится.
            DB::table('clan_warehouses')->whereIn('item_id', $itemIds)->delete();
            Item::query()->whereKey($itemIds)->delete();
        });

        return $count;
    }

    public function activateCurrentStage(int $runId, ?Carbon $now = null): void
    {
        $now ??= now();
        $this->cleanupTargets($runId);
        $this->replenish($runId, $now);
    }

    private function replenish(int $runId, Carbon $now): int
    {
        return DB::transaction(function () use ($runId, $now): int {
            $run = WorldEventRun::query()
                ->with(['event.locations', 'currentStage.targets.item', 'currentStage.targets.monster'])
                ->lockForUpdate()
                ->find($runId);
            if ($run === null
                || $run->currentStage === null
                || $run->status !== WorldEventRun::STATUS_ACTIVE
                || $run->ends_at->lte($now)) {
                return 0;
            }

            $event = $run->event;
            $stage = $run->currentStage;
            $locations = $event->locations->isNotEmpty()
                ? $event->locations
                : Location::query()->where('map_id', $event->map_id)->get();
            $targets = $stage->targets;
            $activeCounts = $this->activeTargetCounts((int) $run->id, $stage->objective_type);
            $activeSpawns = array_sum($activeCounts);
            $remainingPool = (int) $stage->global_limit - (int) $run->collected_count - $activeSpawns;
            $toSpawn = min(max(0, (int) $stage->spawn_limit - $activeSpawns), max(0, $remainingPool));
            if ($toSpawn === 0 || $locations->isEmpty() || $targets->isEmpty()) {
                $run->next_spawn_at = null;
                $run->save();

                return 0;
            }

            $spawned = 0;
            for ($i = 0; $i < $toSpawn; $i++) {
                $target = $this->chooseTarget($targets, $activeCounts, $stage->objective_type);
                if ($target === null) {
                    break;
                }

                $location = $locations->random();
                if ($stage->objective_type === WorldEventObjectiveType::COLLECT) {
                    $item = Item::query()->create(['share_item_id' => $target->share_item_id]);
                    $slot = new ItemOnLocation;
                    $slot->item_id = $item->id;
                    $slot->location_id = $location->id;
                    $slot->world_event_run_id = $run->id;
                    $slot->count = 1;
                    $slot->interaction_type = $target->item?->type === ShareItemType::CHEST
                        ? LocationItemInteractionType::OPEN_HERE
                        : LocationItemInteractionType::PICKUP;
                    $slot->expires_at = $run->ends_at;
                    $slot->save();
                } else {
                    $monster = $target->monster;
                    MonsterOnLocation::query()->create([
                        'monster_id' => $monster->id,
                        'location_id' => $location->id,
                        'world_event_run_id' => $run->id,
                        'hp_now' => $monster->hp,
                        'hp_max' => $monster->hp,
                        'active' => 1,
                        'is_drop_money' => 0,
                        'current_phase' => 1,
                    ]);
                }

                $targetId = $stage->objective_type === WorldEventObjectiveType::COLLECT
                    ? (int) $target->share_item_id
                    : (int) $target->monster_id;
                $activeCounts[$targetId] = ($activeCounts[$targetId] ?? 0) + 1;
                $spawned++;
            }

            $run->next_spawn_at = null;
            $run->save();

            return $spawned;
        });
    }

    /** @return array<int, int> */
    private function activeTargetCounts(int $runId, WorldEventObjectiveType $objectiveType): array
    {
        if ($objectiveType === WorldEventObjectiveType::COLLECT) {
            return ItemOnLocation::query()
                ->join('items', 'items.id', '=', 'item_on_locations.item_id')
                ->where('item_on_locations.world_event_run_id', $runId)
                ->selectRaw('items.share_item_id AS target_id, COUNT(*) AS target_count')
                ->groupBy('items.share_item_id')
                ->pluck('target_count', 'target_id')
                ->map(fn ($count): int => (int) $count)
                ->all();
        }

        return MonsterOnLocation::query()
            ->where('world_event_run_id', $runId)
            ->where('active', 1)
            ->selectRaw('monster_id AS target_id, COUNT(*) AS target_count')
            ->groupBy('monster_id')
            ->pluck('target_count', 'target_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    /** @param Collection<int, WorldEventStageTarget> $targets */
    private function chooseTarget(Collection $targets, array $activeCounts, WorldEventObjectiveType $objectiveType): ?WorldEventStageTarget
    {
        $eligible = $targets->filter(function (WorldEventStageTarget $target) use ($activeCounts, $objectiveType): bool {
            $targetId = $objectiveType === WorldEventObjectiveType::COLLECT
                ? (int) $target->share_item_id
                : (int) $target->monster_id;

            return $target->max_active === null
                || ($activeCounts[$targetId] ?? 0) < (int) $target->max_active;
        });
        $totalWeight = $eligible->sum(fn (WorldEventStageTarget $target): int => max(1, (int) $target->spawn_weight));
        if ($totalWeight === 0) {
            return null;
        }

        $roll = random_int(1, $totalWeight);
        foreach ($eligible as $target) {
            $roll -= max(1, (int) $target->spawn_weight);
            if ($roll <= 0) {
                return $target;
            }
        }

        return null;
    }

    private function deleteGroundItems(int $runId): void
    {
        $itemIds = ItemOnLocation::query()->where('world_event_run_id', $runId)->pluck('item_id');
        ItemOnLocation::query()->where('world_event_run_id', $runId)->delete();
        Item::query()->whereKey($itemIds)->delete();
    }

    private function cleanupTargets(int $runId): void
    {
        $this->deleteGroundItems($runId);
        MonsterOnLocation::query()
            ->where('world_event_run_id', $runId)
            ->where('active', 1)
            ->update(['active' => 0]);
    }
}
