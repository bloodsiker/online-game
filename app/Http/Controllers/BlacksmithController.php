<?php

namespace App\Http\Controllers;

use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\ShareItem;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlacksmithController extends Controller
{
    public function index(Request $request, $id)
    {
        $user = Auth::user();
        $blacksmith = Structure::find($id);

        if (!$blacksmith) {
            abort(404);
        }

        $recipes = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('share_items.type', ShareItem::TYPE_RECIPE)
            ->get();

        $resources = DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItem::TYPE_RESOURCE)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'count' => $item->count,
                ];
            })->toArray();

        return view('blacksmith.kraft', compact('blacksmith', 'user', 'recipes', 'resources'));
    }

    public function kraftItem(Request $request, $id)
    {
        $user = Auth::user();

        $recipeItem = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $id)
            ->first();

        if (!$recipeItem instanceof Backpack) {
            abort(404);
        }

        $resources = DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItem::TYPE_RESOURCE)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'count' => $item->count,
                ];
            })->toArray();

        $recipe = $recipeItem->item->itemInfo->recipe;
        $itemsNeedKraft = $recipe->items;

        $allowKraft = true;
        foreach ($itemsNeedKraft as $itemsKraft) {
            foreach ($resources as $resource) {
                if ($itemsKraft->id === $resource['id']) {
                    if ($resource['count'] < $itemsKraft->pivot->count) {
                        $allowKraft = false;
                    }
                }
            }
        }

        if ($allowKraft) {
            $percentKraft = mt_rand(0, 100);
            if ($percentKraft <= $recipe->percent) {

                $successKraftItem = new Item();
                $successKraftItem->share_item_id = $recipe->kraftItem->id;
                $successKraftItem->save();

                $user->backpack()->attach($successKraftItem->id, ['equipped' => 0, 'count' => 1]);

                session()->flash('message', sprintf('Успешний крафт. Получено %s', $recipe->kraftItem->name));
            } else {
                session()->flash('message', 'Не удачный крафт');
            }

            $recipeItem->item->delete();
            $recipeItem->delete();

            foreach ($itemsNeedKraft as $itemDelete) {
                $itemBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $itemDelete->id])
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->first();

                if ($itemBackpack->count > $itemDelete->pivot->count) {
                    $itemBackpack->count -= $itemDelete->pivot->count;
                    $itemBackpack->save();
                } else {
                    $itemBackpack->delete();
                    Item::where('id', $itemBackpack->item_id)->delete();
                }
            }

        } else {
            session()->flash('message', 'Не достаточно ресурсов для крафта');
        }

        return redirect()->back();
    }

    public function breakItem($id, Request $request)
    {
        $user = Auth::user();
        $blacksmith = Structure::find($id);

        if (!$blacksmith) {
            abort(404);
        }

        $crystal = ShareItem::find(17);

        if ($request->has('iid')) {
            $itemId = $request->get('iid');
            $item = Backpack::with(['item', 'item.itemInfo'])->where(['item_id' => $itemId])->first();
            if (!$item instanceof Backpack) {
                session()->flash('message', 'Не найден предмет для кристализации');
                return redirect()->back();
            }

            $hasBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $crystal->id])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->first();

            $countCrystal = $item->item->itemInfo->break_crystal;

            if ($hasBackpack instanceof Backpack && $crystal->type === ShareItem::TYPE_RESOURCE) {
                $hasBackpack->count += $countCrystal;
                $hasBackpack->save();

                $item->delete();
            } else {
                $newItem = new Item();
                $newItem->share_item_id = $crystal->id;
                $newItem->save();

                $backpack = new Backpack();
                $backpack->user_id = $user->id;
                $backpack->item_id = $newItem->id;
                $backpack->count = $countCrystal;
                $backpack->save();
            }

            $item->delete();
            $item->item->delete();

            session()->flash('message', sprintf('Вы получили кристаллов в количестве %s шт', $countCrystal));
            return redirect()->back();
        }

        $items = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [ShareItem::TYPE_WEAPON, ShareItem::TYPE_SHIELD, ShareItem::TYPE_ARMOR])
            ->get();

        return view('blacksmith.break', compact('blacksmith', 'user', 'items', 'crystal'));
    }
}
