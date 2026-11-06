<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventPlayerProgress;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class GetLocationWorldEvents
{
    /** @return list<array<string, mixed>> */
    public function execute(User $user): array
    {
        $runs = WorldEventRun::query()->where('status', WorldEventRun::STATUS_ACTIVE)
            ->where('ends_at', '>', now())->whereHas('currentStage', fn ($query) => $query->where('is_active', true))
            ->whereHas('event', function ($query) use ($user): void {
                $query->where(function ($query) use ($user): void {
                    $query->whereHas('locations', fn ($locations) => $locations->where('locations.id', $user->location_id))
                        ->orWhere(function ($query) use ($user): void {
                            $query->whereDoesntHave('locations')->whereHas('map.locations', fn ($locations) => $locations->where('locations.id', $user->location_id));
                        });
                });
            })->with(['event', 'currentStage.targets.item', 'currentStage.targets.monster'])->orderBy('ends_at')->get();
        $progress = WorldEventPlayerProgress::query()->where('user_id', $user->id)
            ->whereIn('world_event_run_id', $runs->modelKeys())->get()
            ->keyBy(fn ($row): string => $row->world_event_run_id.':'.$row->world_event_stage_id);

        return $runs->map(function (WorldEventRun $run) use ($progress): array {
            $stage = $run->currentStage;
            $personal = (int) ($progress->get($run->id.':'.$stage->id)?->collected_count ?? 0);
            $collect = $stage->objective_type === WorldEventObjectiveType::COLLECT;

            return [
                'run_id' => (int) $run->id,
                'title' => $run->event->title,
                'url' => route('events', ['mode' => 'events']).'#event-run-'.$run->id,
                'ends_at' => $run->ends_at->toIso8601String(),
                'global' => (int) $run->collected_count,
                'global_limit' => (int) $stage->global_limit,
                'personal' => $personal,
                'player_limit' => (int) $stage->player_limit,
                'limit_reached' => $personal >= (int) $stage->player_limit,
                'instruction' => __($collect ? 'local-events.collect' : 'local-events.kill'),
                'targets' => $stage->targets->map(fn ($target) => $collect ? $target->item?->name : $target->monster?->name)->filter()->implode(', '),
                'item_ids' => $collect ? $stage->targets->pluck('share_item_id')->filter()->map(fn ($id): int => (int) $id)->values()->all() : [],
            ];
        })->all();
    }
}
