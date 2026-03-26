<?php

namespace App\Http\Controllers;

use App\Enums\ShareItemType;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionClaim;
use App\Models\Auction\AuctionHistory;
use App\Models\Auction\AuctionOrder;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Models\User;
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

            if ($hasBackpack instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
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

            if ($hasBackpack instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
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

    public function exchange(Request $request, int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $query = AuctionOrder::where('structure_id', $auction->id)
            ->where('user_id', '!=', $user->id)
            ->with(['shareItem', 'user']);

        $matchingOnly = $request->input('filter.matching', '1') === '1';
        if ($matchingOnly) {
            $userShareItemIds = Backpack::select('items.share_item_id')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $user->id)
                ->where('backpacks.equipped', 0)
                ->where('share_items.is_sell', 1)
                ->pluck('items.share_item_id');

            $query->whereIn('share_item_id', $userShareItemIds);
        }

        if ($request->filled('filter.name')) {
            $query->whereHas('shareItem', fn ($q) => $q->where('name', 'like', '%' . $request->input('filter.name') . '%'));
        }

        if ($request->filled('filter.type')) {
            $query->whereHas('shareItem', fn ($q) => $q->where('type', $request->input('filter.type')));
        }

        if ($request->filled('filter.count_min')) {
            $query->where('count', '>=', (int) $request->input('filter.count_min'));
        }

        if ($request->filled('filter.count_max')) {
            $query->where('count', '<=', (int) $request->input('filter.count_max'));
        }

        $orders = $query->orderByDesc('price')->get();
        $filter = $request->input('filter', []);

        return view('auction.exchange', compact('auction', 'user', 'orders', 'filter'));
    }

    public function myOrders(int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $orders = AuctionOrder::where(['structure_id' => $auction->id, 'user_id' => $user->id])
            ->with('shareItem')
            ->get();

        return view('auction.my_orders', compact('auction', 'user', 'orders'));
    }

    public function newOrder(Request $request, int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $selectedItem = null;
        if ($request->has('siid')) {
            $selectedItem = ShareItem::where('id', $request->get('siid'))->where('is_sell', 1)->first();
        }

        $shareItems = ShareItem::where('is_sell', 1)->orderBy('name')->get();

        return view('auction.new_order', compact('auction', 'user', 'shareItems', 'selectedItem'));
    }

    public function newOrderSave(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $shareItemId = (int) $request->input('form.share_item_id');
        $count = max(1, (int) $request->input('form.count', 1));
        $price = max(1, (int) $request->input('form.price', 1));

        $shareItem = ShareItem::where('id', $shareItemId)->where('is_sell', 1)->first();
        if (!$shareItem) {
            return $this->redirectWithMessage('Предмет не найден.');
        }

        $totalCost = 100 + ($count * $price);
        if ($user->money < $totalCost) {
            return $this->redirectWithMessage(sprintf('Недостаточно монет. Нужно %d (100 комиссия + %d эскроу).', $totalCost, $count * $price));
        }

        DB::transaction(function () use ($user, $auction, $shareItem, $count, $price, $request) {
            $user->decrement('money', 100 + ($count * $price));

            AuctionOrder::create([
                'user_id'       => $user->id,
                'structure_id'  => $auction->id,
                'share_item_id' => $shareItem->id,
                'count'         => $count,
                'price'         => $price,
                'is_anonymous'  => $request->input('form.is_anonymous') ? 1 : 0,
            ]);

            session()->flash('message', sprintf('Заявка на покупку «%s» (%d шт. по %d) создана', $shareItem->name, $count, $price));
        });

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function cancelOrder(int $id, int $orderId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $order = AuctionOrder::where(['id' => $orderId, 'user_id' => $user->id])->first();
        if (!$order instanceof AuctionOrder) {
            return $this->redirectWithMessage('Заявка не найдена.');
        }

        DB::transaction(function () use ($user, $order) {
            $refund = $order->count * $order->price;
            $user->increment('money', $refund);
            session()->flash('message', sprintf('Заявка отменена. Возвращено %d монет', $refund));
            $order->delete();
        });

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function fulfillOrder(Request $request, int $id, int $orderId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $order = AuctionOrder::where(['id' => $orderId, 'structure_id' => $id])
            ->lockForUpdate()
            ->first();

        if (!$order instanceof AuctionOrder) {
            return $this->redirectWithMessage('Заявка не найдена или уже выполнена.');
        }

        if ($order->user_id === $user->id) {
            return $this->redirectWithMessage('Нельзя выполнить собственную заявку.');
        }

        $sellCount = max(1, (int) $request->get('count', 1));
        $sellCount = min($sellCount, $order->count);

        $slotItem = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('items.share_item_id', $order->share_item_id)
            ->where('backpacks.equipped', 0)
            ->with('item')
            ->first();

        if (!$slotItem instanceof Backpack) {
            return $this->redirectWithMessage('У вас нет этого предмета в сумке.');
        }

        $sellCount = min($sellCount, $slotItem->count);
        $totalPayment = $sellCount * $order->price;
        $fee = $this->recalculate($totalPayment);

        if ($user->money < $fee) {
            return $this->redirectWithMessage(sprintf('Недостаточно монет для оплаты налога (%d монет).', $fee));
        }

        DB::transaction(function () use ($user, $order, $slotItem, $sellCount, $totalPayment, $fee, $auction) {
            // Снимаем предметы с продавца
            if ($slotItem->count === $sellCount) {
                Backpack::where(['user_id' => $user->id, 'item_id' => $slotItem->item_id])->delete();
            } else {
                $slotItem->decrement('count', $sellCount);
            }

            // Продавец получает деньги, платит налог
            $user->increment('money', $totalPayment);
            $user->decrement('money', $fee);

            // Резервируем предметы для покупателя (claim — он заберёт сам)
            $shareItem = $order->shareItem;

            if ($slotItem->count === $sellCount) {
                // Полный перенос — используем существующий item_id
                $claimItemId = $slotItem->item_id;
            } else {
                // Частичный — создаём новый Item-запись для покупателя
                $newItem = Item::create(['share_item_id' => $order->share_item_id]);
                $claimItemId = $newItem->id;
            }

            AuctionClaim::create([
                'user_id'      => $order->user_id,
                'structure_id' => $auction->id,
                'item_id'      => $claimItemId,
                'count'        => $sellCount,
            ]);

            // Обновляем или удаляем заявку
            $order->count -= $sellCount;
            if ($order->count <= 0) {
                $order->delete();
            } else {
                $order->save();
            }

            AuctionHistory::create([
                'buy_user_id'  => $order->user_id,
                'sell_user_id' => $user->id,
                'structure_id' => $auction->id,
                'item_id'      => $slotItem->item_id,
                'count'        => $sellCount,
                'price'        => $totalPayment,
            ]);

            session()->flash('message', sprintf(
                'Продано «%s» %d шт. Получено %d монет (налог %d)',
                $shareItem->name, $sellCount, $totalPayment - $fee, $fee
            ));
        });

        return redirect()->route('auction.exchange', ['id' => $auction->id]);
    }

    public function claims(int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $claims = AuctionClaim::where(['user_id' => $user->id, 'structure_id' => $auction->id])
            ->with('item')
            ->get();

        return view('auction.claims', compact('auction', 'user', 'claims'));
    }

    public function claimTake(int $id, int $claimId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $claim = AuctionClaim::where(['id' => $claimId, 'user_id' => $user->id])->first();
        if (!$claim instanceof AuctionClaim) {
            return $this->redirectWithMessage('Предмет не найден.');
        }

        DB::transaction(function () use ($user, $claim) {
            $shareItem = $claim->item->itemInfo;

            $existingSlot = Backpack::select('backpacks.*')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->where('backpacks.user_id', $user->id)
                ->where('items.share_item_id', $shareItem->id)
                ->first();

            if ($existingSlot instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existingSlot->increment('count', $claim->count);
            } else {
                $user->backpack()->attach($claim->item_id, ['equipped' => 0, 'count' => $claim->count]);
            }

            $claim->delete();
        });

        return redirect()->route('auction.claims', ['id' => $auction->id]);
    }

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        return redirect()->back();
    }
}
