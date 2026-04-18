<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Http\Request;
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

        $location->load(['map', 'northSide', 'southSide', 'eastSide', 'westSide', 'upSide', 'downSide']);

        return view('admin.location.info', compact('location'));
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
    }
}
