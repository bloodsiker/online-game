<?php

namespace App\Http\Controllers;

use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\ShareItem;
use App\Models\Structure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index($id)
    {
        $user = Auth::user();
        $shop = Structure::with('shopItems.item', 'shopItems.requirements.item')->find($id);

        if (!$shop) {
            abort(404);
        }

        return view('shop.buy', compact('shop', 'user'));
    }

    public function buyItem(Request $request, $id, $itemId): RedirectResponse
    {
        $user = Auth::user();

        $shareItem = ShareItem::find($itemId);
        $countBuy = $request->integer('count', 1);
        $amountToBuy = $countBuy * $shareItem->price;

        if ($user->money >= $amountToBuy) {
            $user->money -= $amountToBuy;
            $user->save();

            $hasBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $shareItem->id])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->first();

            if ($hasBackpack instanceof Backpack && $shareItem->type === ShareItem::TYPE_RESOURCE) {
                $hasBackpack->count += $countBuy;
                $hasBackpack->save();
            } else {
                for ($i = 1; $i <= $countBuy; $i++) {
                    $item = new Item();
                    $item->share_item_id = $shareItem->id;
                    $item->count_use = $shareItem->count_use;
                    $item->save();

                    $user->backpack()->attach($item->id, [
                        'equipped' => 0,
                        'count' => 1
                    ]);
                }
            }

            session()->flash('message', sprintf('Куплено %s %s шт', $shareItem->name, $countBuy));
        } else {
            session()->flash('message', 'Не достаточно монет для покупки.');
        }

        return redirect()->back();
    }

    public function sellItem(Request $request, $id)
    {
        $user = Auth::user();
        $shop = Structure::find($id);

        if (!$shop) {
            abort(404);
        }

        if ($request->isMethod('POST')) {
            $checkedItems = $request->input('item');
            $sellItems = array_filter($checkedItems, function($product) {
                return isset($product['selected']) && $product['selected'] == 1;
            });

            if (!$sellItems || count($sellItems) === 0) {
                session()->flash('message', 'Не выбраны предметы для продажи');
                return redirect()->back();
            }

            $items = Backpack::select('backpacks.*')
                ->with(['item'])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $user->id)
                ->whereIn('item_id', array_keys($sellItems))
                ->where('equipped', 0)
                ->where('share_items.is_sell', 1)
                ->get();

            $sellTotalPrice = 0;
            $idsToDelete = [];
            foreach ($items as $sellItem) {
                $countItem = $sellItems[$sellItem->item_id];
                if ($countItem['count'] < $sellItem->count) {
                    $sellTotalPrice += round($sellItem->item->itemInfo->price / 2) * $countItem['count'];

                    $sellItem->count -= $countItem['count'];
                    $sellItem->save();
                } else {
                    $sellTotalPrice += round($sellItem->item->itemInfo->price / 2) * $sellItem->count;
                    $idsToDelete[] = $sellItem->item_id;
                }

            }

            $user->money += $sellTotalPrice;
            $user->save();

            Backpack::select('backpacks.*')->whereIn('item_id', $idsToDelete)->delete();
            Item::whereIn('id', $idsToDelete)->delete();

            session()->flash('message', sprintf('Продано на %s монет', number_format($sellTotalPrice, 0, ',', ' ')));
        }


        $itemsToSell = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('equipped', 0)
            ->where('share_items.is_sell', 1)
            ->orderBy('items.share_item_id', 'desc')
            ->get();

        return view('shop.sell', compact('shop', 'user', 'itemsToSell'));
    }
}
