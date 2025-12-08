<?php

namespace App\Http\Controllers;

use App\Models\Auction\Auction;
use App\Models\Auction\AuctionHistory;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\ShareItem;
use App\Models\Structure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index($id)
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = Auction::where(['structure_id' => $auction->id])->get();

        return view('auction.list', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLot($id)
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = Auction::where(['structure_id' => $auction->id, 'user_id' => $user->id])->get();

        return view('auction.list_my_lot', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLotEdit(int $id, int $slotId)
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $itemEdit = Auction::find($slotId);
        if (!$itemEdit instanceof Auction) {
            return $this->redirectWithMessage('Такого предмета нет в коммисионном магазине!');
        }

        return view('auction.edit_lot', compact('auction', 'user', 'itemEdit'));
    }

    public function myLotCancel(int $id, int $slotId)
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $itemTake = Auction::where(['id' => $slotId, 'user_id' => $user->id])->first();
        if (!$itemTake instanceof Auction) {
            return $this->redirectWithMessage('Такого предмета нет в коммисионном магазине!');
        }

        DB::transaction(function () use ($user, $itemTake) {
            $shareItem = $itemTake->item->itemInfo;

            $hasBackpack = Backpack::select('backpacks.*')
                ->where('items.share_item_id', $shareItem->id)
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->first();

            if ($hasBackpack instanceof Backpack && $shareItem->type === ShareItem::TYPE_RESOURCE) {
                $hasBackpack->increment('count', $itemTake->count);
            } else {
                $user->backpack()->attach($itemTake->item->id, [
                    'equipped' => 0,
                    'count' => $itemTake->count
                ]);
            }

            session()->flash('message', sprintf('Вы забрали %s %s шт', $shareItem->name, $itemTake->count));

            $itemTake->delete();
        });

        return redirect()->route('auction.my_lot', ['id' => $auction->id]);
    }

    public function newLot(Request $request, int $id)
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $selectedItem = null;
        if ($request->has('iid')) {
            $selectedItem = Backpack::where(['item_id' => $request->get('iid')])->with(['item'])->first();
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

        return view('auction.new_lot', compact('auction', 'user', 'itemsToSell', 'selectedItem'));
    }

    public function newLotSave(Request $request, $id)
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $fee = $this->recalculate($request->input('form.price'));
        if ($user->money < $fee) {
            return $this->redirectWithMessage('Не достаточно монет что бы оплатить налог.');
        }

        $slotItem = Backpack::where(['item_id' => $request->input('form.item_id')])->with(['item'])->first();
        if (!$slotItem instanceof Backpack) {
            return $this->redirectWithMessage('Не найдено предмета в сумке.');
        }

        DB::transaction(function () use ($user, $auction, $slotItem, $fee, $request) {
            $newSlot = new Auction();
            $newSlot->user_id = $user->id;
            $newSlot->structure_id = $auction->id;
            $newSlot->item_id = $slotItem->item->id;
            $newSlot->count = $request->get('for.amount') > $slotItem->count ? $slotItem->count : $request->input('form.amount');
            $newSlot->is_anonymous = $request->input('form.is_anonymous') ? 1 : 0;
            $newSlot->price = $request->input('form.price');
            $newSlot->save();

            if ($slotItem->count === $newSlot->count) {
                Backpack::select('backpacks.*')->where('item_id', $slotItem->item->id)->delete();
            }

            if ($slotItem->count > $newSlot->count) {
//                $slotItem->decrement('count', $slotItem->count - $newSlot->count);
                $slotItem->count = $slotItem->count - $newSlot->count;
                $slotItem->save();
            }

            $user->decrement('money', $fee);

            session()->flash('message', sprintf('%s выставлен на продажу', $slotItem->item->itemInfo->name));
        });

        return redirect()->route('auction.new_lot', ['id' => $auction->id]);
    }

    protected function log10Custom($x) {
        return log($x) / log(10);
    }

    protected function recalculate($price, $rate = 1) {
        if ($price <= 0) {
            return 0;
        }

        $res = pow(0.5, $this->log10Custom($price) + 2) * $price * $rate;
        $res = ceil($res);

        return ($res <= 0 || is_nan($res)) ? 0 : $res;
    }

    public function buyItem(Request $request, $id, $itemId): RedirectResponse
    {
        $user = Auth::user();

        $auction = Structure::find($id);
        if (!$auction instanceof Structure) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        if ($auction->location_id !== $user->location_id) {
            return $this->redirectWithMessage('Вы не находитесь рядом с Комиссионным магазином!');
        }

        $itemBuy = Auction::where(['structure_id' => $id, 'item_id' => $itemId])->lockForUpdate()->first();
        if (!$itemBuy instanceof Auction) {
            return $this->redirectWithMessage('Этого предмета уже нет в продаже');
        }

        if ($user->money < $itemBuy->price) {
            return $this->redirectWithMessage('Не достаточно монет для покупки.');
        }

        DB::transaction(function () use ($user, $itemBuy) {
            $user->decrement('money', $itemBuy->price);

            $shareItem = $itemBuy->item->itemInfo;

            $hasBackpack = Backpack::select('backpacks.*')
                ->where('items.share_item_id', $shareItem->id)
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->first();

            if ($hasBackpack instanceof Backpack && $shareItem->type === ShareItem::TYPE_RESOURCE) {
                $hasBackpack->increment('count', $itemBuy->count);
            } else {
                $user->backpack()->attach($itemBuy->item->id, [
                    'equipped' => 0,
                    'count' => $itemBuy->count
                ]);
            }

            AuctionHistory::create([
                'buy_user_id' => $user->id,
                'sell_user_id' => $itemBuy->user_id,
                'structure_id' => $itemBuy->structure_id,
                'item_id' => $itemBuy->item_id,
                'count' => $itemBuy->count,
                'price' => $itemBuy->price,
            ]);

            session()->flash('message', sprintf('Куплено %s %s шт', $shareItem->name, $itemBuy->count));

            $itemBuy->delete();
        });

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

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        return redirect()->back();
    }
}
