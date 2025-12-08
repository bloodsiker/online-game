<?php

namespace App\Http\Controllers;

use App\Models\Backpack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BackpackController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('equipped', 0);

        $countQuery = clone $query;
        $countItems = $countQuery->whereIn('type', ['heal', 'weapon', 'shield', 'armor', 'resource', 'recipe'])->count();


        if ($request->has('sid') && $request->get('sid')) {
            $query->where('items.share_item_id', $request->get('sid'));
        }

        $group = $request->get('group', 'main');

        if ($group === 'main') {
            $query->whereIn('type', ['heal', 'weapon', 'shield', 'armor', 'resource', 'recipe']);
        } elseif ($group === 'key') {
            $query->whereIn('type', ['key']);
        }  elseif ($group === 'quest') {
            $query->whereIn('type', ['quest']);
        }  elseif ($group === 'artifact') {
            $query->whereIn('type', ['artifact']);
        } elseif ($group === 'gift') {
            $query->whereIn('type', ['gift']);
        }

        $backpack = $query->orderBy('items.share_item_id', 'desc')->get();

        $items = [];
        foreach ($backpack as $item) {
            $items[$item->item->itemInfo->type][] = $item;
        }

        $playerEquip = $user->player->playerEquip;

        return view('backpack.index', compact('backpack', 'group', 'items', 'playerEquip', 'countItems'));
    }
}
