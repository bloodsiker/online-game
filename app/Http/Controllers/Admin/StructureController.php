<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Npc;
use App\Models\ShareAction;
use App\Models\ShareItem;
use App\Models\Structure;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function list()
    {
        $listStructures = Structure::query()->orderByDesc('id')->get();

        return view('admin.structures.list', compact('listStructures'));
    }

    public function info(Request $request, Structure $structure)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $structure->fill($data);
            $structure->save();

            return redirect()->back();
        }

        $locations = Location::query()->orderByDesc('id')->get();
        $npcs = Npc::query()->orderByDesc('id')->get();

        return view('admin.structures.info', compact('structure', 'locations', 'npcs'));
    }

    public function infoShop(Request $request, Structure $structure)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $structure->shopItems()->attach($data['share_item_id']);

            return redirect()->back();
        }

         $allItems = ShareItem::all();

        return view('admin.structures.info_shop', compact('structure', 'allItems'));
    }

    public function infoShopDeleteItem(Structure $structure, ShareItem $item)
    {
        $structure->shopItems()->detach($item->id);

        return redirect()->back();
    }

    public function infoAction(Request $request, Structure $structure)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $structure->actions()->attach($data['share_action_id']);

            return redirect()->back();
        }

        $allActions = ShareAction::all();

        return view('admin.structures.info_action', compact('structure', 'allActions'));
    }

    public function infoActionDelete(Structure $structure, ShareAction $action)
    {
        $structure->actions()->detach($action->id);

        return redirect()->back();
    }
}
