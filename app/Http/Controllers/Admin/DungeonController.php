<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Dungeon\Domain\Enums\DungeonDeathBehavior;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $dungeon->death_return_location_id = $deathReturnLocationId;
            $dungeon->save();

            return redirect()->back()->with('success', 'Настройки смерти в данже сохранены.');
        }

        $dungeon->load(['firstLocation', 'exitLocation', 'returnLocation', 'deathReturnLocation']);
        $deathBehaviors = DungeonDeathBehavior::cases();
        $locations = $dungeon->locations()->orderBy('id')->get();

        return view('admin.dungeon.info', compact('dungeon', 'deathBehaviors', 'locations'));
    }
}
