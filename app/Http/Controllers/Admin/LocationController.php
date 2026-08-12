<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function list(): View
    {
        $listLocations = Location::with('map')->orderByDesc('id')->get();

        return view('admin.location.list', compact('listLocations'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $location = new Location;
            $this->fillLocation($location, $request);
            $location->save();

            return redirect()->route('admin.location.info', $location->id)->with('success', 'Локация создана.');
        }

        return view('admin.location.create');
    }

    public function info(Request $request, Location $location): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillLocation($location, $request);
            $location->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $location->load(['map', 'northSide', 'southSide', 'eastSide', 'westSide', 'upSide', 'downSide', 'monsters']);
        $monsters = Monster::orderBy('name')->get();

        return view('admin.location.info', compact('location', 'monsters'));
    }

    public function addMonster(Request $request, Location $location): RedirectResponse
    {
        $aggression = $request->input('aggression');

        $location->monsters()->attach($request->input('monster_id'), [
            'aggression' => $aggression !== '' && $aggression !== null ? (int) $aggression : null,
        ]);

        return redirect()->back()->with('success', 'Моб добавлен.');
    }

    public function updateMonsterAggression(Request $request, Location $location, Monster $monster): RedirectResponse
    {
        $aggression = $request->input('aggression');

        $location->monsters()->updateExistingPivot($monster->id, [
            'aggression' => $aggression !== '' && $aggression !== null ? (int) $aggression : null,
        ]);

        return redirect()->back()->with('success', 'Агрессия обновлена.');
    }

    public function deleteMonster(Location $location, Monster $monster): RedirectResponse
    {
        $location->monsters()->detach($monster->id);

        return redirect()->back()->with('success', 'Моб удалён.');
    }

    private function fillLocation(Location $location, Request $request): void
    {
        $location->name = $request->input('name');
        $location->description = $request->input('description');
        $location->map_id = (int) $request->input('map_id');
        $location->is_locked = (bool) $request->input('is_locked', false);
        $location->north = $request->input('north') ?: null;
        $location->south = $request->input('south') ?: null;
        $location->east = $request->input('east') ?: null;
        $location->west = $request->input('west') ?: null;
        $location->up = $request->input('up') ?: null;
        $location->down = $request->input('down') ?: null;
        $location->count_monster = (int) $request->input('count_monster', 0);
        $location->percent_respawn_monster = (int) $request->input('percent_respawn_monster', 0);
        $location->time_not_attack = (int) $request->input('time_not_attack', 0);

        if ($request->hasFile('image')) {
            $location->image = $this->storeImage($request->file('image'));
        }
    }

    private function storeImage(UploadedFile $file): string
    {
        return $file->store('locations', 'public');
    }
}
