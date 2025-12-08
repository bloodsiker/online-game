<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Map;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function list()
    {
        $list = Map::query()->orderByDesc('id')->get();

        return view('admin.map.list', compact('list'));
    }

    public function info(Request $request, Map $map)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $map->fill($data);
            $map->save();

            return redirect()->route('admin.maps');
        }

        $maps = Map::query()->orderByDesc('id')->get();

        return view('admin.map.info', compact('map', 'maps'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('POST')) {

            $data = $request->toArray();

            $npc = new Map();
            $npc->fill($data);
            $npc->save();

            return redirect()->route('admin.maps');
        }

        $maps = Map::query()->orderByDesc('id')->get();

        return view('admin.map.create', compact('maps'));
    }

    public function location(Request $request, Map $map)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $map->fill($data);
            $map->save();

            return redirect()->route('admin.maps');
        }

        $locations = Location::where(['map_id' => $map->id])->orderByDesc('id')->get();

        return view('admin.map.location', compact('map', 'locations'));
    }
}
