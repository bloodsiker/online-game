<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShareItem;
use App\Models\ShareRecipe;
use App\Models\Skill;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function list()
    {
        $listItems = ShareItem::query()->orderByDesc('id')->get();

        return view('admin.item.list', compact('listItems'));
    }

    public function info(Request $request, ShareItem $item)
    {
        if ($request->isMethod('POST')) {
            $data = $request->toArray();

            $item->fill($data);
            $item->save();

            return redirect()->back();
        }

        $item->load(['recipe', 'recipe.items']);

        $allItems = ShareItem::whereNot('id', $item->id)->get();
        $skills = Skill::all();

        return view('admin.item.info', compact('item', 'allItems', 'skills'));
    }

    public function addItemToRecipe(Request $request, ShareRecipe $recipe)
    {
        $data = $request->toArray();
        $recipe->items()->attach($data['share_item_id'], ['count' => $data['count']]);

        return redirect()->back();
    }

    public function deleteItemInRecipe(Request $request, ShareRecipe $recipe, ShareItem $item)
    {
        $recipe->items()->detach($item->id);

        return redirect()->back();
    }
}
