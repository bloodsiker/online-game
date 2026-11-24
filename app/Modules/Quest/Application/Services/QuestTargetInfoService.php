<?php

declare(strict_types=1);

namespace App\Modules\Quest\Application\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestPlayer;
use Illuminate\Support\Collection;

/**
 * Данные для страницы «Задания»: какие мобы/NPC относятся к целям, на каких картах их искать
 * и кому сдавать квест.
 */
final class QuestTargetInfoService
{
    /**
     * @param  Collection<int, QuestPlayer>  $questPlayers
     * @return array{objectives: array<int, array{monsters: list<array{name: string, lvl: int, url: string}>, maps: list<array{name: string, url: string}>, npc: ?array{name: string, url: string, map: ?array{name: string, url: string}}}>, turnIn: array<int, ?array{name: string, url: string, map: ?array{name: string, url: string}}>}
     */
    public function build(Collection $questPlayers): array
    {
        $objectives = $questPlayers
            ->flatMap(fn (QuestPlayer $qp) => $qp->quest->objectives)
            ->unique('id');

        $monsterIds = $objectives
            ->filter(fn (QuestObjective $o) => $o->target_type === 'monster')
            ->flatMap(fn (QuestObjective $o) => $this->monsterIds($o))
            ->unique()
            ->values();

        $monsters = Monster::query()
            ->whereIn('id', $monsterIds)
            ->with('locations.map')
            ->get()
            ->keyBy('id');

        $targetLocations = Location::query()
            ->whereIn('id', $objectives
                ->filter(fn (QuestObjective $objective) => $objective->target_type === 'location' && $objective->target_id)
                ->pluck('target_id')
                ->unique())
            ->with('map')
            ->get()
            ->keyBy('id');

        $npcIds = $objectives
            ->filter(fn (QuestObjective $o) => $o->target_type === 'npc' && $o->target_id)
            ->pluck('target_id')
            ->merge($questPlayers->flatMap(fn (QuestPlayer $qp) => array_filter([
                $qp->currentStage?->complete_npc_id,
                $qp->quest->complete_npc_id,
            ])))
            ->unique()
            ->values();

        $npcs = Npc::query()->whereIn('id', $npcIds)->with('location.map')->get()->keyBy('id');

        $result = ['objectives' => [], 'turnIn' => []];

        foreach ($objectives as $objective) {
            $linked = $this->monsterIds($objective)->map(fn (int $id) => $monsters->get($id))->filter();

            $result['objectives'][$objective->id] = [
                'monsters' => $linked->map(fn (Monster $m) => [
                    'name' => (string) $m->name,
                    'lvl' => (int) $m->lvl,
                    'url' => route('info.monster.catalog', ['id' => $m->id]),
                ])->values()->all(),
                'maps' => $this->maps(
                    $linked,
                    $objective->map_id ? $objective->map : null,
                    $objective->target_type === 'location'
                        ? $targetLocations->get((int) $objective->target_id)
                        : null,
                ),
                'npc' => $objective->target_type === 'npc' ? $this->npc($npcs->get((int) $objective->target_id)) : null,
            ];
        }

        foreach ($questPlayers as $qp) {
            $npcId = $qp->currentStage?->complete_npc_id ?? $qp->quest->complete_npc_id;
            $result['turnIn'][$qp->id] = $npcId ? $this->npc($npcs->get((int) $npcId)) : null;
        }

        return $result;
    }

    /** @return Collection<int, int> */
    private function monsterIds(QuestObjective $objective): Collection
    {
        if ($objective->target_type !== 'monster') {
            return collect();
        }

        $ids = ! empty($objective->target_ids) ? $objective->target_ids : [$objective->target_id];

        return collect($ids)->filter()->map(fn ($id) => (int) $id)->values();
    }

    /**
     * @param  Collection<int, Monster>  $monsters
     * @return list<array{name: string, url: string}>
     */
    private function maps(Collection $monsters, ?Map $requiredMap = null, ?Location $targetLocation = null): array
    {
        if ($targetLocation?->map?->slug !== null) {
            return [[
                'name' => (string) $targetLocation->name.' — '.(string) $targetLocation->map->name,
                'url' => route('map.public', ['slug' => $targetLocation->map->slug]),
            ]];
        }

        if ($requiredMap?->slug !== null) {
            return [[
                'name' => (string) $requiredMap->name,
                'url' => route('map.public', ['slug' => $requiredMap->slug]),
            ]];
        }

        $maps = [];

        foreach ($monsters as $monster) {
            foreach ($monster->locations as $location) {
                $map = $location->map;
                if ($map === null || $map->slug === null || isset($maps[$map->id])) {
                    continue;
                }
                $maps[$map->id] = [
                    'name' => (string) $map->name,
                    'url' => route('map.public', ['slug' => $map->slug]),
                ];
            }
        }

        return array_values($maps);
    }

    /** @return ?array{name: string, url: string, map: ?array{name: string, url: string}} */
    private function npc(?Npc $npc): ?array
    {
        if ($npc === null) {
            return null;
        }

        /** @var ?Location $location */
        $location = $npc->hide_location ? null : $npc->location;
        $map = $location?->map;

        return [
            'name' => (string) $npc->name,
            'url' => route('info.npc', ['uuid' => $npc->uuid]),
            'map' => $map?->slug !== null
                ? [
                    'name' => (string) $map->name,
                    'url' => route('map.public', ['slug' => $map->slug, 'highlight_location' => $location->id]),
                ]
                : null,
        ];
    }
}
