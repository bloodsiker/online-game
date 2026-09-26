<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Dungeon\Domain\Enums\DungeonDeathBehavior;
use App\Modules\Dungeon\Domain\Enums\DungeonStageCompletionType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DungeonController extends Controller
{
    public function list(): View
    {
        $dungeons = Dungeon::query()
            ->with(['firstLocation', 'exitLocation', 'returnLocation', 'deathReturnLocation'])
            ->orderByDesc('id')
            ->get();

        return view('admin.dungeon.list', compact('dungeons'));
    }

    public function info(Request $request, Dungeon $dungeon): View|RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $validated = $request->validate([
                'death_behavior' => ['required', Rule::enum(DungeonDeathBehavior::class)],
                'death_return_location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'type' => ['required', Rule::enum(DungeonType::class)],
            ]);

            $deathReturnLocationId = $validated['death_return_location_id'] ?? null;

            if ($deathReturnLocationId !== null) {
                $belongsToDungeon = Location::query()
                    ->whereKey($deathReturnLocationId)
                    ->where('dungeon_id', $dungeon->id)
                    ->exists();

                if (! $belongsToDungeon) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors([
                            'death_return_location_id' => 'Локация возврата должна принадлежать этому данжу.',
                        ]);
                }
            }

            $dungeon->death_behavior = DungeonDeathBehavior::from($validated['death_behavior']);
            $dungeon->type = DungeonType::from($validated['type']);
            $dungeon->death_return_location_id = $deathReturnLocationId;
            $dungeon->save();

            return redirect()->back()->with('success', 'Настройки смерти в данже сохранены.');
        }

        $dungeon->load(['firstLocation', 'exitLocation', 'returnLocation', 'deathReturnLocation', 'stages.locations', 'stages.monsters.monster']);
        $deathBehaviors = DungeonDeathBehavior::cases();
        $locations = $dungeon->locations()->orderBy('id')->get();
        $monsters = Monster::query()->orderBy('lvl')->orderBy('name')->get(['id', 'name', 'lvl']);

        return view('admin.dungeon.info', compact('dungeon', 'deathBehaviors', 'locations', 'monsters'));
    }

    public function saveStage(Request $request, Dungeon $dungeon): RedirectResponse
    {
        $validated = $request->validate([
            'stage_id' => ['nullable', 'integer', Rule::exists('dungeon_stages', 'id')->where('dungeon_id', $dungeon->id)],
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique('dungeon_stages', 'number')
                    ->where('dungeon_id', $dungeon->id)
                    ->ignore($request->integer('stage_id')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:10', 'max:86400'],
            'spawn_type' => ['required', Rule::enum(DungeonStageSpawnType::class)],
            'total_monsters' => ['required', 'integer', 'min:1', 'max:1000'],
            'location_ids' => ['required', 'array', 'min:1'],
            'location_ids.*' => ['integer', Rule::exists('locations', 'id')->where('dungeon_id', $dungeon->id)],
            'entry_location_id' => ['required', 'integer'],
            'monsters' => ['required', 'array', 'min:1', 'max:50'],
            'monsters.*.monster_id' => ['required', 'integer', 'exists:monsters,id'],
            'monsters.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'monsters.*.weight' => ['required', 'integer', 'min:1', 'max:1000'],
            'monsters.*.location_id' => [
                'nullable',
                'integer',
                Rule::exists('locations', 'id')->where('dungeon_id', $dungeon->id),
            ],
        ]);

        $locationIds = array_map('intval', $validated['location_ids']);
        if (! in_array((int) $validated['entry_location_id'], $locationIds, true)) {
            return back()->withInput()->withErrors(['entry_location_id' => 'Вход должен быть одной из локаций этажа.']);
        }

        foreach ($validated['monsters'] as $index => $monster) {
            $locationId = isset($monster['location_id']) ? (int) $monster['location_id'] : null;
            if ($locationId !== null && ! in_array($locationId, $locationIds, true)) {
                return back()->withInput()->withErrors([
                    "monsters.{$index}.location_id" => 'Локация монстра должна входить в состав этого этажа.',
                ]);
            }
        }

        DB::transaction(function () use ($dungeon, $validated): void {
            $spawnType = DungeonStageSpawnType::from($validated['spawn_type']);
            $totalMonsters = $spawnType === DungeonStageSpawnType::FIXED
                ? array_sum(array_column($validated['monsters'], 'quantity'))
                : (int) $validated['total_monsters'];

            $stage = DungeonStage::query()->updateOrCreate(
                ['id' => $validated['stage_id'] ?? null, 'dungeon_id' => $dungeon->id],
                [
                    'number' => $validated['number'],
                    'name' => $validated['name'],
                    'time_limit_seconds' => $validated['time_limit_seconds'] ?? null,
                    'completion_type' => DungeonStageCompletionType::KILL_ALL,
                    'spawn_type' => $spawnType,
                    'total_monsters' => $totalMonsters,
                ],
            );

            $sync = [];
            foreach (array_values($validated['location_ids']) as $position => $locationId) {
                $sync[(int) $locationId] = [
                    'is_entry' => (int) $locationId === (int) $validated['entry_location_id'],
                    'position' => $position,
                ];
            }
            $stage->locations()->sync($sync);
            $stage->monsters()->delete();
            foreach ($validated['monsters'] as $monster) {
                $stage->monsters()->create([
                    'monster_id' => (int) $monster['monster_id'],
                    'location_id' => $spawnType === DungeonStageSpawnType::FIXED
                        ? ($monster['location_id'] ?? null)
                        : null,
                    'quantity' => (int) $monster['quantity'],
                    'weight' => (int) $monster['weight'],
                ]);
            }
        });

        return back()->with('success', 'Этаж сохранён.');
    }

    public function deleteStage(Dungeon $dungeon, DungeonStage $stage): RedirectResponse
    {
        abort_unless($stage->dungeon_id === $dungeon->id, 404);
        $stage->delete();

        return back()->with('success', 'Этаж удалён.');
    }
}
