<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Location\Infrastructure\Persistence\Models\MapGatheringResource;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MapController extends Controller
{
    public function list(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'parent_id' => $request->query('parent_id', ''),
            'location_id' => $request->query('location_id', ''),
        ];

        $listMaps = Map::with('parent')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('slug', 'like', $search)
                        ->orWhere('folder', 'like', $search);
                });
            })
            ->when($filters['parent_id'] !== '', function ($query) use ($filters): void {
                $query->where('parent_id', (int) $filters['parent_id']);
            })
            ->when($filters['location_id'] !== '', function ($query) use ($filters): void {
                $query->whereHas('locations', function ($query) use ($filters): void {
                    $query->where('id', (int) $filters['location_id']);
                });
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $allMaps = Map::orderBy('name')->get();

        return view('admin.map.list', compact('listMaps', 'filters', 'allMaps'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $map = Map::create([
                'name' => $request->input('name'),
                'slug' => $request->input('slug'),
                'folder' => $request->input('folder'),
                'parent_id' => $request->input('parent_id') ?: null,
            ]);

            return redirect()->route('admin.map.info', $map->id)->with('success', 'Карта создана.');
        }

        $allMaps = Map::orderBy('name')->get();

        return view('admin.map.create', compact('allMaps'));
    }

    public function info(Request $request, Map $map): mixed
    {
        if ($request->isMethod('POST')) {
            $map->name = $request->input('name');
            $map->slug = $request->input('slug');
            $map->folder = $request->input('folder');
            $map->parent_id = $request->input('parent_id') ?: null;
            $map->has_gathering_field = (bool) $request->input('has_gathering_field', false);

            if ($request->hasFile('gathering_field_image')) {
                $oldImage = $map->getRawOriginal('gathering_field_image');
                $map->gathering_field_image = $this->storeGatheringFieldImage($request->file('gathering_field_image'));
                $this->deleteStorageImage($oldImage);
            } elseif ($request->boolean('delete_gathering_field_image')) {
                $this->deleteStorageImage($map->getRawOriginal('gathering_field_image'));
                $map->gathering_field_image = null;
            }

            $map->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $allMaps = Map::where('id', '!=', $map->id)->orderBy('name')->get();
        $locations = Location::where('map_id', $map->id)->orderByDesc('id')->get();
        $mapResources = $map->gatheringResources()->with(['resource.skill', 'nodes.attempts'])->orderBy('id')->get();
        $gatheringResources = ShareItem::query()
            ->where('type', ShareItemType::RESOURCE->value)
            ->whereNotNull('gathering_time_seconds')
            ->whereNotNull('gathering_respawn_seconds')
            ->whereNotNull('gathering_tool_family')
            ->with('skill')
            ->orderBy('name')
            ->get();

        return view('admin.map.info', compact('map', 'allMaps', 'locations', 'mapResources', 'gatheringResources'));
    }

    public function location(Request $request, Map $map): RedirectResponse
    {
        $location = Location::findOrFail((int) $request->input('location_id'));
        $location->map_id = $map->id;
        $location->save();

        return redirect()->back()->with('success', 'Локация добавлена на карту.');
    }

    public function saveGatheringResource(Request $request, Map $map): RedirectResponse
    {
        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'max_active' => ['required', 'integer', 'min:1', 'max:100'],
            'min_x' => ['required', 'integer', 'between:1,99'],
            'max_x' => ['required', 'integer', 'between:1,99', 'gt:min_x'],
            'min_y' => ['required', 'integer', 'between:1,99'],
            'max_y' => ['required', 'integer', 'between:1,99', 'gt:min_y'],
        ]);

        $resource = ShareItem::query()->findOrFail($data['share_item_id']);
        abort_unless($resource->type === ShareItemType::RESOURCE, 422);

        $configuration = MapGatheringResource::query()->updateOrCreate(
            ['map_id' => $map->id, 'share_item_id' => $resource->id],
            $data,
        );

        $extraNodes = max(0, $configuration->nodes()->count() - $configuration->max_active);
        if ($extraNodes > 0) {
            $nodeIds = $configuration->nodes()
                ->whereDoesntHave('attempts')
                ->orderByDesc('id')
                ->limit($extraNodes)
                ->pluck('id');
            $configuration->nodes()->whereIn('id', $nodeIds)->delete();
        }

        return redirect()->back()->with('success', 'Ресурс карты сохранён.');
    }

    public function updateGatheringResource(
        Request $request,
        Map $map,
        MapGatheringResource $resource,
    ): RedirectResponse {
        abort_unless((int) $resource->map_id === (int) $map->id, 404);

        $data = $request->validate([
            'max_active' => ['required', 'integer', 'min:1', 'max:100'],
            'min_x' => ['required', 'integer', 'between:1,99'],
            'max_x' => ['required', 'integer', 'between:1,99', 'gt:min_x'],
            'min_y' => ['required', 'integer', 'between:1,99'],
            'max_y' => ['required', 'integer', 'between:1,99', 'gt:min_y'],
        ]);

        $busyNodes = $resource->nodes()->whereHas('attempts')->count();
        if ($busyNodes > $data['max_active']) {
            throw ValidationException::withMessages([
                'max_active' => sprintf(
                    'Нельзя уменьшить количество ниже %d: столько ресурсов сейчас добывают.',
                    $busyNodes,
                ),
            ]);
        }

        $boundsChanged = (int) $resource->min_x !== (int) $data['min_x']
            || (int) $resource->max_x !== (int) $data['max_x']
            || (int) $resource->min_y !== (int) $data['min_y']
            || (int) $resource->max_y !== (int) $data['max_y'];

        DB::transaction(function () use ($resource, $data, $boundsChanged): void {
            $resource->update($data);

            $extraNodes = max(0, $resource->nodes()->count() - $resource->max_active);
            if ($extraNodes > 0) {
                $nodeIds = $resource->nodes()
                    ->whereDoesntHave('attempts')
                    ->orderByDesc('id')
                    ->limit($extraNodes)
                    ->pluck('id');
                $resource->nodes()->whereIn('id', $nodeIds)->delete();
            }

            if (! $boundsChanged) {
                return;
            }

            $resource->nodes()
                ->whereDoesntHave('attempts')
                ->eachById(function ($node) use ($resource): void {
                    $node->x_percent = random_int($resource->min_x * 100, $resource->max_x * 100) / 100;
                    $node->y_percent = random_int($resource->min_y * 100, $resource->max_y * 100) / 100;
                    $node->save();
                });
        });

        return redirect()
            ->to(route('admin.map.info', $map->id).'#tab-resources')
            ->with('success', 'Настройки ресурса на карте обновлены.');
    }

    public function deleteGatheringResource(Map $map, MapGatheringResource $resource): RedirectResponse
    {
        abort_unless((int) $resource->map_id === (int) $map->id, 404);
        $resource->delete();

        return redirect()->back()->with('success', 'Ресурс удалён с карты.');
    }

    private function storeGatheringFieldImage(UploadedFile $file): string
    {
        return $file->store('maps/gathering-fields', 'public');
    }
}
