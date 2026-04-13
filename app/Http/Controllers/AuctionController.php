<?php

declare(strict_types=1);

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index(int $id)
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = Auction::where('structure_id', $auction->id)
            ->with(['item.itemInfo'])
            ->get();

        return view('auction.list', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLot(int $id)
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = Auction::where('structure_id', $auction->id)
            ->where('user_id', $user->id)
            ->with(['item.itemInfo'])
            ->get();

        return view('auction.list_my_lot', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLotEdit(int $id, int $slotId)
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $itemEdit = Auction::where('id', $slotId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('auction.edit_lot', compact('auction', 'user', 'itemEdit'));
    }

    public function myLotCancel(int $id, int $slotId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        DB::transaction(function () use ($user, $auction, $slotId) {
            // lockForUpdate — защита от двойного снятия лота
            $itemTake = Auction::where('id', $slotId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$itemTake instanceof Auction) {
                return;
            }

            $shareItem = $itemTake->item->itemInfo;

            $existing = $this->findBackpackSlot($user->id, $shareItem->id);

            if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->increment('count', $itemTake->count);
            } else {
                $user->backpack()->attach($itemTake->item->id, [
                    'equipped' => 0,
                    'count'    => $itemTake->count,
                ]);
            }

            session()->flash('message', sprintf('Вы забрали %s %s шт', $shareItem->name, $itemTake->count));

            $itemTake->delete();
        });

        return redirect()->route('auction.my_lot', ['id' => $auction->id]);
    }

    public function newLot(Request $request, int $id)
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (!$auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $selectedItem = null;
        if ($request->filled('iid')) {
            $selectedItem = Backpack::where('item_id', (int) $request->get('iid'))
                ->where('user_id', $user->id)
                ->with('item')
                ->first();
        }

        $itemsToSell = $this->getSellableBackpackItems($user->id);

        return view('auction.new_lot', compact('auction', 'user', 'itemsToSell', 'selectedItem'));
    }

    public function newLotSave(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'form.item_id' => 'required|integer',
            'form.amount'  => 'required|integer|min:1',
            'form.price'   => 'required|integer|min:1',
            'form.is_anonymous' => 'nullable|boolean',
        ]);

        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $fee = $this->recalculate((int) $data['form']['price']);
        if ($user->money < $fee) {
            return $this->redirectWithMessage('Не достаточно монет что бы оплатить налог.');
        }

        DB::transaction(function () use ($user, $auction, $data, $fee) {
            // lockForUpdate — защита от одновременного выставления одного предмета
            $slotItem = Backpack::where('item_id', (int) $data['form']['item_id'])
                ->where('user_id', $user->id)  // FIX: обязательная проверка владельца
                ->where('equipped', 0)
                ->lockForUpdate()
                ->with('item.itemInfo')
                ->first();

            if (!$slotItem instanceof Backpack) {
                session()->flash('message', 'Не найдено предмета в сумке.');
                return;
            }

            $amount = min((int) $data['form']['amount'], $slotItem->count);

            $newSlot = Auction::create([
                'user_id'      => $user->id,
                'structure_id' => $auction->id,
                'item_id'      => $slotItem->item->id,
                'count'        => $amount,
                'is_anonymous' => (bool) ($data['form']['is_anonymous'] ?? false),
                'price'        => (int) $data['form']['price'],
            ]);

            if ($slotItem->count <= $amount) {
                $slotItem->delete();
            } else {
                $slotItem->decrement('count', $amount);
            }

            $user->decrement('money', $fee);

            session()->flash('message', sprintf('%s выставлен на продажу', $slotItem->item->itemInfo->name));
        });

        return redirect()->route('auction.new_lot', ['id' => $auction->id]);
    }

    public function buyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        if ($auction->location_id !== $user->location_id) {
            return $this->redirectWithMessage('Вы не находитесь рядом с Комиссионным магазином!');
        }

        DB::transaction(function () use ($user, $auction, $id, $itemId) {
            $itemBuy = Auction::where('structure_id', $id)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if (!$itemBuy instanceof Auction) {
                session()->flash('message', 'Этого предмета уже нет в продаже');
                return;
            }

            // FIX: запрет покупки своего лота
            if ($itemBuy->user_id === $user->id) {
                session()->flash('message', 'Нельзя купить собственный лот.');
                return;
            }

            // FIX: проверка денег внутри транзакции с блокировкой
            $freshUser = \App\Models\User::lockForUpdate()->find($user->id);
            if ($freshUser->money < $itemBuy->price) {
                session()->flash('message', 'Не достаточно монет для покупки.');
                return;
            }

            $freshUser->decrement('money', $itemBuy->price);

            $shareItem = $itemBuy->item->itemInfo;
            $existing  = $this->findBackpackSlot($user->id, $shareItem->id);

            if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->increment('count', $itemBuy->count);
            } else {
                $user->backpack()->attach($itemBuy->item->id, [
                    'equipped' => 0,
                    'count'    => $itemBuy->count,
                ]);
            }

            AuctionHistory::create([
                'buy_user_id'  => $user->id,
                'sell_user_id' => $itemBuy->user_id,
                'structure_id' => $itemBuy->structure_id,
                'item_id'      => $itemBuy->item_id,
                'count'        => $itemBuy->count,
                'price'        => $itemBuy->price,
            ]);

            session()->flash('message', sprintf('Куплено %s %s шт', $shareItem->name, $itemBuy->count));

            $itemBuy->delete();
        });

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id)
    {
        $user = Auth::user();
        $shop = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $checkedItems = array_filter(
                (array) $request->input('item', []),
                fn ($product) => isset($product['selected']) && $product['selected'] == 1
            );

            if (empty($checkedItems)) {
                session()->flash('message', 'Не выбраны предметы для продажи');
                return redirect()->back();
            }

            $sellTotalPrice = DB::transaction(function () use ($user, $checkedItems): int {
                $items = Backpack::select('backpacks.*')
                    ->with('item.itemInfo')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                    ->where('backpacks.user_id', $user->id)
                    ->whereIn('item_id', array_keys($checkedItems))
                    ->where('equipped', 0)
                    ->where('share_items.is_sell', 1)
                    ->lockForUpdate()
                    ->get();

                $total      = 0;
                $idsToDelete = [];

                foreach ($items as $sellItem) {
                    $requested = (int) ($checkedItems[$sellItem->item_id]['count'] ?? 0);
                    $qty       = min($requested, $sellItem->count);
                    $total    += (int) round($sellItem->item->itemInfo->price / 2) * $qty;

                    if ($qty >= $sellItem->count) {
                        $idsToDelete[] = $sellItem->item_id;
                    } else {
                        $sellItem->decrement('count', $qty);
                    }
                }

                if (!empty($idsToDelete)) {
                    Backpack::whereIn('item_id', $idsToDelete)->where('user_id', $user->id)->delete();
                    Item::whereIn('id', $idsToDelete)->delete();
                }

                $user->increment('money', $total);

                return $total;
            });

            session()->flash('message', sprintf('Продано на %s монет', number_format($sellTotalPrice, 0, ',', ' ')));
        }

        $itemsToSell = $this->getSellableBackpackItems($user->id);

        return view('shop.sell', compact('shop', 'user', 'itemsToSell'));
    }

    public function exchange(Request $request, int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $query = AuctionOrder::where('structure_id', $auction->id)
            ->where('user_id', '!=', $user->id)
            ->with(['shareItem', 'user']);

        if ($request->input('filter.matching', '1') === '1') {
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
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $orders = AuctionOrder::where('structure_id', $auction->id)
            ->where('user_id', $user->id)
            ->with('shareItem')
            ->get();

        return view('auction.my_orders', compact('auction', 'user', 'orders'));
    }

    public function newOrder(Request $request, int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $selectedItem = null;
        if ($request->filled('siid')) {
            $selectedItem = ShareItem::where('id', (int) $request->get('siid'))
                ->where('is_sell', 1)
                ->first();
        }

        $shareItems = ShareItem::where('is_sell', 1)->orderBy('name')->get();

        return view('auction.new_order', compact('auction', 'user', 'shareItems', 'selectedItem'));
    }

    public function newOrderSave(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'form.share_item_id' => 'required|integer',
            'form.count'         => 'required|integer|min:1',
            'form.price'         => 'required|integer|min:1',
            'form.is_anonymous'  => 'nullable|boolean',
        ]);

        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $shareItem = ShareItem::where('id', (int) $data['form']['share_item_id'])
            ->where('is_sell', 1)
            ->first();

        if (!$shareItem) {
            return $this->redirectWithMessage('Предмет не найден.');
        }

        $count     = (int) $data['form']['count'];
        $price     = (int) $data['form']['price'];
        $totalCost = 100 + ($count * $price);

        DB::transaction(function () use ($user, $auction, $shareItem, $count, $price, $totalCost, $data) {
            $freshUser = \App\Models\User::lockForUpdate()->find($user->id);

            if ($freshUser->money < $totalCost) {
                session()->flash('message', sprintf(
                    'Недостаточно монет. Нужно %d (100 комиссия + %d эскроу).',
                    $totalCost,
                    $count * $price
                ));
                return;
            }

            $freshUser->decrement('money', $totalCost);

            AuctionOrder::create([
                'user_id'       => $user->id,
                'structure_id'  => $auction->id,
                'share_item_id' => $shareItem->id,
                'count'         => $count,
                'price'         => $price,
                'is_anonymous'  => (bool) ($data['form']['is_anonymous'] ?? false),
            ]);

            session()->flash('message', sprintf(
                'Заявка на покупку «%s» (%d шт. по %d) создана',
                $shareItem->name, $count, $price
            ));
        });

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function cancelOrder(int $id, int $orderId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        DB::transaction(function () use ($user, $orderId) {
            $order = AuctionOrder::where('id', $orderId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$order instanceof AuctionOrder) {
                session()->flash('message', 'Заявка не найдена.');
                return;
            }

            $refund = $order->count * $order->price;
            $user->increment('money', $refund);

            session()->flash('message', sprintf('Заявка отменена. Возвращено %d монет', $refund));

            $order->delete();
        });

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function fulfillOrder(Request $request, int $id, int $orderId): RedirectResponse
    {
        $data = $request->validate([
            'count' => 'nullable|integer|min:1',
        ]);

        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        DB::transaction(function () use ($user, $auction, $id, $orderId, $data) {
            $order = AuctionOrder::where('id', $orderId)
                ->where('structure_id', $id)
                ->lockForUpdate()
                ->first();

            if (!$order instanceof AuctionOrder) {
                session()->flash('message', 'Заявка не найдена или уже выполнена.');
                return;
            }

            if ($order->user_id === $user->id) {
                session()->flash('message', 'Нельзя выполнить собственную заявку.');
                return;
            }

            $slotItem = Backpack::select('backpacks.*')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->where('backpacks.user_id', $user->id)
                ->where('items.share_item_id', $order->share_item_id)
                ->where('backpacks.equipped', 0)
                ->lockForUpdate()
                ->with('item')
                ->first();

            if (!$slotItem instanceof Backpack) {
                session()->flash('message', 'У вас нет этого предмета в сумке.');
                return;
            }

            $sellCount    = min((int) ($data['count'] ?? 1), $order->count, $slotItem->count);
            $totalPayment = $sellCount * $order->price;
            $fee          = $this->recalculate($totalPayment);

            // FIX: проверка денег на налог внутри транзакции
            $freshSeller = \App\Models\User::lockForUpdate()->find($user->id);
            if ($freshSeller->money < $fee) {
                session()->flash('message', sprintf('Недостаточно монет для оплаты налога (%d монет).', $fee));
                return;
            }

            // Снимаем предметы с продавца
            if ($slotItem->count <= $sellCount) {
                Backpack::where('user_id', $user->id)
                    ->where('item_id', $slotItem->item_id)
                    ->delete();
            } else {
                $slotItem->decrement('count', $sellCount);
            }

            // Продавец получает деньги, платит налог
            $freshSeller->increment('money', $totalPayment - $fee);

            // Клейм для покупателя
            $claimItemId = ($slotItem->count <= $sellCount)
                ? $slotItem->item_id
                : Item::create(['share_item_id' => $order->share_item_id])->id;

            AuctionClaim::create([
                'user_id'      => $order->user_id,
                'structure_id' => $auction->id,
                'item_id'      => $claimItemId,
                'count'        => $sellCount,
            ]);

            $order->count -= $sellCount;
            $order->count <= 0 ? $order->delete() : $order->save();

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
                $order->shareItem->name, $sellCount, $totalPayment - $fee, $fee
            ));
        });

        return redirect()->route('auction.exchange', ['id' => $auction->id]);
    }

    public function claims(int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $claims = AuctionClaim::where('user_id', $user->id)
            ->where('structure_id', $auction->id)
            ->with('item.itemInfo')
            ->get();

        return view('auction.claims', compact('auction', 'user', 'claims'));
    }

    public function claimTake(int $id, int $claimId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        DB::transaction(function () use ($user, $claimId) {
            $claim = AuctionClaim::where('id', $claimId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$claim instanceof AuctionClaim) {
                session()->flash('message', 'Предмет не найден.');
                return;
            }

            $shareItem = $claim->item->itemInfo;
            $existing  = $this->findBackpackSlot($user->id, $shareItem->id);

            if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->increment('count', $claim->count);
            } else {
                $user->backpack()->attach($claim->item_id, ['equipped' => 0, 'count' => $claim->count]);
            }

            $claim->delete();
        });

        return redirect()->route('auction.claims', ['id' => $auction->id]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function findBackpackSlot(int $userId, int $shareItemId): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $userId)
            ->where('items.share_item_id', $shareItemId)
            ->where('backpacks.equipped', 0)
            ->first();
    }

    private function getSellableBackpackItems(int $userId)
    {
        return Backpack::select('backpacks.*')
            ->with(['item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->where('backpacks.equipped', 0)
            ->where('share_items.is_sell', 1)
            ->orderBy('items.share_item_id', 'desc')
            ->get();
    }

    private function recalculate(int $price, float $rate = 1.0): int
    {
        if ($price <= 0) {
            return 0;
        }

        $res = pow(0.5, log10($price) + 2) * $price * $rate;
        $res = (int) ceil($res);

        return ($res <= 0) ? 0 : $res;
    }

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        return redirect()->back();
    }
}