<?php

declare(strict_types=1);

namespace App\Modules\Event\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Event\Domain\Services\WorldEventLifecycleService;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEventStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class WorldEventController extends Controller
{
    public function __construct(
        private readonly WorldEventLifecycleService $lifecycle,
        private readonly AdminImageStorage $imageStorage,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'map_id' => ['nullable', 'integer', 'exists:maps,id'],
            'influence_map_id' => ['nullable', 'integer', 'exists:maps,id'],
            'share_item_id' => ['nullable', 'integer', 'exists:share_items,id'],
        ]);

        $mapId = isset($filters['map_id']) ? (int) $filters['map_id'] : null;
        $influenceMapId = isset($filters['influence_map_id']) ? (int) $filters['influence_map_id'] : null;
        $shareItemId = isset($filters['share_item_id']) ? (int) $filters['share_item_id'] : null;

        return view('event::admin.world-events.index', [
            'events' => WorldEvent::query()
                ->with(['map', 'influenceMap', 'item', 'monster', 'locations', 'stages.targets.item', 'stages.targets.monster', 'activeRun.currentStage.targets'])
                ->when($mapId !== null, fn ($query) => $query->where('map_id', $mapId))
                ->when($influenceMapId !== null, fn ($query) => $query->where(function ($query) use ($influenceMapId): void {
                    $query->where('influence_map_id', $influenceMapId)
                        ->orWhere(function ($query) use ($influenceMapId): void {
                            $query->whereNull('influence_map_id')->where('map_id', $influenceMapId);
                        });
                }))
                ->when($shareItemId !== null, fn ($query) => $query->where(function ($query) use ($shareItemId): void {
                    $query->where('share_item_id', $shareItemId)
                        ->orWhereHas('stages', fn ($stageQuery) => $stageQuery->where(function ($stageQuery) use ($shareItemId): void {
                            $stageQuery->where('share_item_id', $shareItemId)
                                ->orWhereHas('targets', fn ($targetQuery) => $targetQuery->where('share_item_id', $shareItemId));
                        }));
                }))
                ->latest()
                ->get(),
            'maps' => Map::query()->orderBy('name')->get(['id', 'name']),
            'filterItem' => $shareItemId !== null
                ? ShareItem::query()->find($shareItemId, ['id', 'name'])
                : null,
            'filters' => [
                'map_id' => $mapId,
                'influence_map_id' => $influenceMapId,
                'share_item_id' => $shareItemId,
            ],
        ]);
    }

    public function create(): View
    {
        return view('event::admin.world-events.create', ['event' => null] + $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $locations = $data['location_ids'] ?? [];
        $stages = $data['stages'];
        unset($data['location_ids'], $data['stages']);

        $event = new WorldEvent;
        $event->fill($data);
        $this->fillImage($event, $request);
        $event->save();
        $event->locations()->sync($locations);
        $this->syncStages($event, $stages);

        return redirect()->route('admin.event.world-events.index')->with('success', 'Событие создано.');
    }

    public function edit(WorldEvent $event): View
    {
        $event->load(['locations', 'stages.targets']);

        return view('event::admin.world-events.edit', ['event' => $event] + $this->formData());
    }

    public function update(Request $request, WorldEvent $event): RedirectResponse
    {
        if ($event->activeRun()->exists()) {
            return redirect()->back()->with('error', 'Перед изменением этапов завершите текущий запуск события.');
        }

        $data = $this->validated($request);
        $locations = $data['location_ids'] ?? [];
        $stages = $data['stages'];
        unset($data['location_ids'], $data['stages']);

        $event->fill($data);
        $this->fillImage($event, $request);
        $event->save();
        $event->locations()->sync($locations);
        $this->syncStages($event, $stages);

        return redirect()->route('admin.event.world-events.index')->with('success', 'Событие обновлено.');
    }

    public function start(WorldEvent $event): RedirectResponse
    {
        $run = $this->lifecycle->start($event->id);

        return redirect()->back()->with($run === null ? 'error' : 'success', $run === null
            ? 'Событие уже идёт или на выбранной карте нет локаций.'
            : 'Событие запущено.');
    }

    public function finish(WorldEvent $event): RedirectResponse
    {
        $run = $event->activeRun()->first();
        if ($run !== null) {
            $this->lifecycle->finish($run->id);
        }

        return redirect()->back()->with('success', 'Событие завершено.');
    }

    public function destroy(WorldEvent $event): RedirectResponse
    {
        if ($event->activeRun()->exists()) {
            return redirect()->back()->with('error', 'Сначала завершите текущий запуск события.');
        }

        $image = $event->getRawOriginal('image');
        $event->delete();
        $this->deleteStorageImage($image);

        return redirect()->route('admin.event.world-events.index')->with('success', 'Событие удалено.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:4096'],
        ]);

        $path = $this->imageStorage->storeOnPublicDisk($data['file'], 'events/content');

        return response()->json([
            'url' => resolve_storage_image_url($path),
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $mapId = (int) $request->input('map_id');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'delete_image' => ['nullable', 'boolean'],
            'map_id' => ['required', 'integer', 'exists:maps,id'],
            'influence_map_id' => ['required', 'integer', 'exists:maps,id'],
            'influence_name' => ['nullable', 'string', 'max:255'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => [
                'integer',
                Rule::exists('locations', 'id')->where('map_id', $mapId),
            ],
            'next_start_at' => ['nullable', 'date'],
            'repeat_interval_minutes' => ['nullable', 'integer', 'min:1'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'stages' => ['required', 'array', 'min:1', 'max:20'],
            'stages.*.id' => ['nullable', 'integer'],
            'stages.*.title' => ['required', 'string', 'max:150'],
            'stages.*.objective_type' => ['required', Rule::enum(WorldEventObjectiveType::class)],
            'stages.*.global_limit' => ['required', 'integer', 'min:1'],
            'stages.*.player_limit' => ['required', 'integer', 'min:1'],
            'stages.*.spawn_limit' => ['required', 'integer', 'min:1'],
            'stages.*.respawn_seconds' => ['required', 'integer', 'min:60'],
            'stages.*.item_lifetime_minutes' => ['required', 'integer', 'min:1'],
            'stages.*.influence_per_item' => ['required', 'integer', 'min:0'],
            'stages.*.targets' => ['required', 'array', 'min:1', 'max:20'],
            'stages.*.targets.*.id' => ['nullable', 'integer'],
            'stages.*.targets.*.share_item_id' => ['nullable', 'integer', 'exists:share_items,id'],
            'stages.*.targets.*.monster_id' => ['nullable', 'integer', 'exists:monsters,id'],
            'stages.*.targets.*.spawn_weight' => ['required', 'integer', 'min:1', 'max:10000'],
            'stages.*.targets.*.max_active' => ['nullable', 'integer', 'min:1'],
        ]);

        foreach ($data['stages'] as $index => &$stage) {
            $objectiveType = WorldEventObjectiveType::from($stage['objective_type']);
            $stage['objective_type'] = $objectiveType;

            $seenTargetIds = [];
            foreach ($stage['targets'] as $targetIndex => &$target) {
                $target['share_item_id'] = $objectiveType === WorldEventObjectiveType::COLLECT
                    ? ($target['share_item_id'] ?? null)
                    : null;
                $target['monster_id'] = $objectiveType === WorldEventObjectiveType::KILL
                    ? ($target['monster_id'] ?? null)
                    : null;
                $targetId = $objectiveType === WorldEventObjectiveType::COLLECT
                    ? $target['share_item_id']
                    : $target['monster_id'];
                $targetField = $objectiveType === WorldEventObjectiveType::COLLECT ? 'share_item_id' : 'monster_id';

                if ($targetId === null) {
                    throw ValidationException::withMessages([
                        "stages.{$index}.targets.{$targetIndex}.{$targetField}" => $objectiveType === WorldEventObjectiveType::COLLECT
                            ? 'Выберите собираемый предмет.'
                            : 'Выберите событийного монстра.',
                    ]);
                }
                if (isset($seenTargetIds[(int) $targetId])) {
                    throw ValidationException::withMessages([
                        "stages.{$index}.targets.{$targetIndex}.{$targetField}" => 'Эта цель уже добавлена в этап.',
                    ]);
                }
                $seenTargetIds[(int) $targetId] = true;

                if ($objectiveType === WorldEventObjectiveType::COLLECT) {
                    $item = ShareItem::query()->with('instantReward')->find((int) $targetId);
                    if ($item?->instantReward !== null) {
                        throw ValidationException::withMessages([
                            "stages.{$index}.targets.{$targetIndex}.share_item_id" => 'Мгновенную награду нельзя использовать как собираемый предмет события.',
                        ]);
                    }
                }
            }
            unset($target);

            $firstTarget = $stage['targets'][0];
            $stage['share_item_id'] = $firstTarget['share_item_id'];
            $stage['monster_id'] = $firstTarget['monster_id'];
        }
        unset($stage);

        $firstStage = $data['stages'][0];
        $data['objective_type'] = $firstStage['objective_type'];
        $data['share_item_id'] = $firstStage['share_item_id'];
        $data['monster_id'] = $firstStage['monster_id'];
        foreach (['global_limit', 'player_limit', 'spawn_limit', 'respawn_seconds', 'item_lifetime_minutes', 'influence_per_item'] as $field) {
            $data[$field] = $firstStage[$field];
        }
        unset($data['image'], $data['delete_image']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function fillImage(WorldEvent $event, Request $request): void
    {
        if ($request->hasFile('image')) {
            $oldImage = $event->getRawOriginal('image');
            $event->image = $this->imageStorage->storeOnPublicDisk($request->file('image'), 'events');
            $this->deleteStorageImage($oldImage);
        } elseif ($request->boolean('delete_image')) {
            $this->deleteStorageImage($event->getRawOriginal('image'));
            $event->image = null;
        }
    }

    /** @param list<array<string, mixed>> $stages */
    private function syncStages(WorldEvent $event, array $stages): void
    {
        $event->allStages()->update(['position' => DB::raw('position + 1000')]);
        $keptIds = [];

        foreach (array_values($stages) as $index => $stageData) {
            $targets = $stageData['targets'];
            unset($stageData['targets']);
            $stageId = isset($stageData['id'])
                ? $event->allStages()->whereKey((int) $stageData['id'])->value('id')
                : null;
            unset($stageData['id']);
            $stageData['position'] = $index + 1;
            $stageData['is_active'] = true;

            $stage = $stageId === null
                ? $event->allStages()->create($stageData)
                : tap($event->allStages()->findOrFail($stageId))->update($stageData);
            $keptIds[] = (int) $stage->id;
            $this->syncTargets($stage, $targets);
        }

        $event->allStages()->whereNotIn('id', $keptIds)->update(['is_active' => false]);
    }

    /** @param list<array<string, mixed>> $targets */
    private function syncTargets(WorldEventStage $stage, array $targets): void
    {
        $stage->allTargets()->update(['position' => DB::raw('position + 1000')]);
        $keptIds = [];

        foreach (array_values($targets) as $index => $targetData) {
            $targetId = isset($targetData['id'])
                ? $stage->allTargets()->whereKey((int) $targetData['id'])->value('id')
                : null;
            unset($targetData['id']);
            $targetData['position'] = $index + 1;
            $targetData['is_active'] = true;

            $target = $targetId === null
                ? $stage->allTargets()->create($targetData)
                : tap($stage->allTargets()->findOrFail($targetId))->update($targetData);
            $keptIds[] = (int) $target->id;
        }

        $stage->allTargets()->whereNotIn('id', $keptIds)->delete();
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'maps' => Map::query()->orderBy('name')->get(['id', 'parent_id', 'name']),
            'locations' => Location::query()->with('map:id,name')->orderBy('map_id')->orderBy('id')->get(['id', 'map_id', 'name']),
            'items' => ShareItem::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'monsters' => Monster::query()->orderBy('name')->get(['id', 'name', 'lvl']),
            'objectiveTypes' => WorldEventObjectiveType::cases(),
        ];
    }
}
