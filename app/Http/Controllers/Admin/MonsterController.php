<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Monster\Monster;
use App\Models\ShareItem;
use Illuminate\Http\Request;

class MonsterController extends Controller
{
    public function list()
    {
        $listMonsters = Monster::query()->orderByDesc('id')->get();

        return view('admin.monster.list', compact('listMonsters'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $npc = new Monster();
            $npc->fill($data);
            $npc->save();

            return redirect()->route('admin.monsters');
        }

        return view('admin.monster.create');
    }

    public function info(Request $request, Monster $monster)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $monster->fill($data);
            $monster->save();

            return redirect()->back();
        }

        return view('admin.monster.info', compact('monster'));
    }

    public function infoDrop(Request $request, Monster $monster)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $monster->items()->attach($data['share_item_id'], [
                'drop_chance' => $data['drop_chance'],
                'min_count' => $data['min_count'],
                'max_count' => $data['max_count'],
            ]);

            return redirect()->back();
        }

        $allItems = ShareItem::all();

        return view('admin.monster.info_drop', compact('monster', 'allItems'));
    }

    public function location(Request $request, Monster $monster)
    {
        if ($request->isMethod('POST')) {
            $monster->locations()->attach($request->get('location_id'));

            return redirect()->back();
        }

        $locations = Location::query()->orderByDesc('id')->get();

        return view('admin.monster.info_location', compact('monster', 'locations'));
    }

    public function deleteLocation(Monster $monster, Location $location)
    {
        $monster->locations()->detach($location->id);

        return redirect()->back();
    }

    public function infoDropDeleteItem(Monster $monster, ShareItem $item)
    {
        $monster->items()->detach($item->id);

        return redirect()->back();
    }
}
