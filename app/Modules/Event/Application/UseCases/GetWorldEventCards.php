<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventFavorite;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluence;
use Illuminate\Support\Collection;

final class GetWorldEventCards
{
    /** @return Collection<int, array<string, mixed>> */
    public function execute(int $userId, string $mode): Collection
    {
        $favoriteEventIds = WorldEventFavorite::query()
            ->where('user_id', $userId)
            ->pluck('world_event_id');

        if ($mode === 'events_my') {
            $events = WorldEvent::query()
                ->with([
                    'map', 'influenceMap', 'item', 'monster', 'locations', 'stages.targets.item', 'stages.targets.monster',
                    'activeRun' => fn ($query) => $query
                        ->where('ends_at', '>', now())
                        ->with([
                            'currentStage.targets.item', 'currentStage.targets.monster',
                            'progresses' => fn ($query) => $query->where('user_id', $userId),
                        ]),
                ])
                ->whereIn('id', $favoriteEventIds)
                ->latest()
                ->limit(30)
                ->get();
            $influences = $this->influences(
                $userId,
                $events->map(fn (WorldEvent $event): int => $event->influenceMapId())->all(),
            );

            return $events->map(fn (WorldEvent $event): array => $this->card(
                event: $event,
                run: $event->activeRun,
                influences: $influences,
                isFavorite: true,
            ));
        }

        if ($mode === 'events_future') {
            $events = WorldEvent::query()
                ->with(['map', 'influenceMap', 'item', 'monster', 'locations', 'stages.targets.item', 'stages.targets.monster'])
                ->where('is_active', true)
                ->whereNotNull('next_start_at')
                ->where('next_start_at', '>', now())
                ->whereDoesntHave('runs', fn ($query) => $query
                    ->where('status', WorldEventRun::STATUS_ACTIVE)
                    ->where('ends_at', '>', now()))
                ->orderBy('next_start_at')
                ->get();
            $influences = $this->influences(
                $userId,
                $events->map(fn (WorldEvent $event): int => $event->influenceMapId())->all(),
            );

            return $events->map(fn (WorldEvent $event): array => $this->card(
                event: $event,
                run: null,
                influences: $influences,
                isFavorite: $favoriteEventIds->contains($event->id),
            ));
        }

        $runs = WorldEventRun::query()
            ->with([
                'event.map', 'event.influenceMap', 'event.item', 'event.monster', 'event.locations',
                'event.stages.targets.item', 'event.stages.targets.monster', 'currentStage.targets.item', 'currentStage.targets.monster',
                'progresses' => fn ($query) => $query->where('user_id', $userId),
            ])
            ->where('status', WorldEventRun::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->latest('starts_at')
            ->limit(20)
            ->get();
        $influences = $this->influences(
            $userId,
            $runs->map(fn (WorldEventRun $run): int => $run->event->influenceMapId())->all(),
        );

        return $runs->map(fn (WorldEventRun $run): array => $this->card(
            event: $run->event,
            run: $run,
            influences: $influences,
            isFavorite: $favoriteEventIds->contains($run->event->id),
        ));
    }

    /** @return array<string, mixed> */
    private function card(WorldEvent $event, ?WorldEventRun $run, Collection $influences, bool $isFavorite): array
    {
        $stage = $run?->currentStage ?? $event->stages->first();
        $progress = $run?->progresses->firstWhere('world_event_stage_id', $stage?->id);
        $stageCount = $event->stages->count();

        return [
            'event' => $event,
            'run' => $run,
            'stage' => $stage,
            'stageCount' => $stageCount,
            'isFavorite' => $isFavorite,
            'progress' => (int) ($progress?->collected_count ?? 0),
            'influenceEarned' => (int) ($progress?->influence_earned ?? 0),
            'mapInfluence' => (int) ($influences->get($event->influenceMapId()) ?? 0),
            'globalPercent' => $run === null || $stage === null
                ? 0
                : min(100, (int) floor($run->collected_count * 100 / max(1, $stage->global_limit))),
            'playerPercent' => min(100, (int) floor(($progress?->collected_count ?? 0) * 100 / max(1, $stage?->player_limit ?? 1))),
        ];
    }

    /** @param list<int> $mapIds */
    private function influences(int $userId, array $mapIds): Collection
    {
        return MapInfluence::query()
            ->where('user_id', $userId)
            ->whereIn('map_id', array_unique($mapIds))
            ->pluck('influence', 'map_id');
    }
}
