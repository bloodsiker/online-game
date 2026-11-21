<?php

namespace App\Http\Controllers;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Location\Infrastructure\Persistence\Models\MapGatheringResource;
use App\Modules\Monster\Domain\Services\MapMonstersCache;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function map()
    {
        return view('maps.city.main.map');
    }

    public function publicMap(string $slug)
    {
        $map = Map::query()->where('slug', $slug)->firstOrFail();
        $folder = trim((string) $map->folder, '/');
        abort_if($folder === '', 404);

        $view = 'maps.'.str_replace('/', '.', $folder).'.map';
        abort_unless(view()->exists($view), 404);

        $currentLocation = Auth::user()?->loadMissing('currentLocation.map')->currentLocation;
        $currentMap = $currentLocation?->map;

        return view($view, [
            'map' => $map,
            'currentMapUrl' => $currentMap?->slug !== null
                ? route('map.public', ['slug' => $currentMap->slug]).'#'.$currentLocation->id
                : null,
        ]);
    }

    public function publicMapMonsters(Map $map): JsonResponse
    {
        $monsters = Cache::remember(
            MapMonstersCache::key($map->id).':'.app()->getLocale(),
            now()->addMinutes(MapMonstersCache::TTL_MINUTES),
            function () use ($map): Collection {
                return Monster::query()
                    ->whereHas('locations', fn ($query) => $query->where('locations.map_id', $map->id))
                    ->orderBy('name->ru')
                    ->get(['id', 'name', 'lvl', 'image', 'is_boss'])
                    ->groupBy('name')
                    ->map(static function ($sameNameMonsters): array {
                        /** @var Monster $monster */
                        $monster = $sameNameMonsters->first();

                        return [
                            'name' => (string) $monster->name,
                            'levels' => $sameNameMonsters
                                ->sortBy('lvl')
                                ->groupBy('lvl')
                                ->map(static function ($sameLevelMonsters): array {
                                    /** @var Monster $monster */
                                    $monster = $sameLevelMonsters->first();

                                    return [
                                        'value' => (int) $monster->lvl,
                                        'info_url' => route('info.monster.catalog', ['id' => $monster->id]),
                                    ];
                                })
                                ->values()
                                ->all(),
                            'image' => $monster->image,
                            'is_boss' => $sameNameMonsters->contains(static fn (Monster $item): bool => $item->isBoss()),
                        ];
                    })
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values();
            },
        );

        return response()->json([
            'map' => $map->name,
            'monsters' => $monsters,
        ]);
    }

    public function publicMapResources(Map $map): JsonResponse
    {
        $resources = MapGatheringResource::query()
            ->where('map_id', $map->id)
            ->with('resource.skill')
            ->get()
            ->pluck('resource')
            ->filter()
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(static fn ($resource): array => [
                'name' => (string) $resource->name,
                'image' => $resource->transparent_image ?? $resource->image,
                'skill_name' => (string) ($resource->skill?->name ?? 'Умение'),
                'required_level' => max(1, (int) $resource->skill_lvl),
                'info_url' => route('items.info.share', ['id' => $resource->id]),
            ]);

        return response()->json([
            'map' => $map->name,
            'resources' => $resources,
        ]);
    }

    public function map2()
    {
        return view('maps.subcity.main.map');
    }

    public function map3()
    {
        return view('maps.catacomb_sacrifice.map');
    }

    public function map4()
    {
        return view('maps.city.sewers.map');
    }

    public function map5()
    {
        return view('maps.subcity.zabytiy_kurgan.map');
    }

    public function map6()
    {
        return view('maps.subcity.overgrown_road.map');
    }

    public function map7()
    {
        return view('maps.subcity.watch_hills.map');
    }

    public function map8()
    {
        return view('maps.subcity.granite_pass.map');
    }
}
