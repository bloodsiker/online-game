<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\Services;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class BestiaryCatalogService
{
    /**
     * @param  array{q?: string, level_from?: int, level_to?: int, map_id?: int, location_id?: int, boss?: string}  $filters
     * @return array{monsters: LengthAwarePaginator, maps: Collection}
     */
    public function get(array $filters): array
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $mapId = (int) ($filters['map_id'] ?? 0);
        $locationId = (int) ($filters['location_id'] ?? 0);
        $boss = (string) ($filters['boss'] ?? 'all');

        $monsters = Monster::query()
            ->with(['locations' => fn ($query) => $query->with('map')->orderBy('name')])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when(isset($filters['level_from']), fn ($query) => $query->where('lvl', '>=', $filters['level_from']))
            ->when(isset($filters['level_to']), fn ($query) => $query->where('lvl', '<=', $filters['level_to']))
            ->when($mapId > 0, fn ($query) => $query->whereHas('locations', fn ($query) => $query->where('map_id', $mapId)))
            ->when($locationId > 0, fn ($query) => $query->whereHas('locations', fn ($query) => $query->whereKey($locationId)))
            ->when($boss === '1', fn ($query) => $query->where('is_boss', true))
            ->when($boss === '0', fn ($query) => $query->where('is_boss', false))
            ->orderBy('lvl')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(24)
            ->withQueryString();

        $maps = Map::query()
            ->whereHas('locations.monsters')
            ->orderBy('name')
            ->get(['id', 'name']);

        return compact('monsters', 'maps');
    }
}
