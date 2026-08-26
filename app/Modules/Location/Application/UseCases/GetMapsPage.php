<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Location\Application\DTOs\MapsPageDTO;
use App\Modules\Location\Application\DTOs\MapTreeNodeDTO;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Support\Collection;

final class GetMapsPage
{
    public function execute(?int $currentMapId = null): MapsPageDTO
    {
        $maps = Map::query()
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'slug'])
            ->keyBy('id');

        $childrenByParent = $maps->groupBy('parent_id');
        $rootMaps = $maps->filter(static fn (Map $map): bool => $map->parent_id === null || ! $maps->has($map->parent_id));
        $visited = [];

        $roots = $rootMaps
            ->map(function (Map $map) use ($childrenByParent, $currentMapId, &$visited): MapTreeNodeDTO {
                return $this->makeNode($map, $childrenByParent, $currentMapId, $visited);
            })
            ->values()
            ->all();

        // Повреждённая циклическая иерархия не должна скрывать карты со страницы.
        foreach ($maps as $map) {
            if (! isset($visited[$map->id])) {
                $roots[] = $this->makeNode($map, $childrenByParent, $currentMapId, $visited);
            }
        }

        return new MapsPageDTO(
            roots: $roots,
            mapsCount: $maps->count(),
            locationsCount: Location::query()->count(),
        );
    }

    /**
     * @param  Collection<int, Collection<int, Map>>  $childrenByParent
     * @param  array<int, true>  $visited
     */
    private function makeNode(Map $map, Collection $childrenByParent, ?int $currentMapId, array &$visited): MapTreeNodeDTO
    {
        $visited[$map->id] = true;
        $children = $childrenByParent->get($map->id, collect())
            ->reject(fn (Map $child): bool => isset($visited[$child->id]))
            ->map(function (Map $child) use ($childrenByParent, $currentMapId, &$visited): MapTreeNodeDTO {
                return $this->makeNode($child, $childrenByParent, $currentMapId, $visited);
            })
            ->values()
            ->all();

        return new MapTreeNodeDTO(
            id: (int) $map->id,
            name: (string) $map->name,
            slug: (string) $map->slug,
            isCurrent: (int) $map->id === $currentMapId,
            children: $children,
        );
    }
}
