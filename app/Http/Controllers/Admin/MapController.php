<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Map;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapController extends Controller
{
    public function list(): View
    {
        $listMaps = Map::with('parent')->orderByDesc('id')->get();

        return view('admin.map.list', compact('listMaps'));
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
            $map->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $allMaps = Map::where('id', '!=', $map->id)->orderBy('name')->get();
        $locations = Location::where('map_id', $map->id)->orderByDesc('id')->get();

        return view('admin.map.info', compact('map', 'allMaps', 'locations'));
    }

    public function location(Request $request, Map $map): RedirectResponse
    {
        $location = Location::findOrFail((int) $request->input('location_id'));
        $location->map_id = $map->id;
        $location->save();

        return redirect()->back()->with('success', 'Локация добавлена на карту.');
    }
}
