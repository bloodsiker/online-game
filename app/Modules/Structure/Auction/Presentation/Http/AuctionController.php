<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Auction\Domain\Models\Auction;
use App\Modules\Structure\Auction\Domain\Models\AuctionClaim;
use App\Modules\Structure\Auction\Domain\Models\AuctionOrder;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Modules\Structure\Auction\Application\UseCases\BuyLot;
use App\Modules\Structure\Auction\Application\UseCases\CancelLot;
use App\Modules\Structure\Auction\Application\UseCases\CancelOrder;
use App\Modules\Structure\Auction\Application\UseCases\CreateLot;
use App\Modules\Structure\Auction\Application\UseCases\CreateOrder;
use App\Modules\Structure\Auction\Application\UseCases\FulfillOrder;
use App\Modules\Structure\Auction\Application\UseCases\SellToShop;
use App\Modules\Structure\Auction\Application\UseCases\TakeClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionController extends Controller
{
    public function __construct(
        private readonly CreateLot $createLot,
        private readonly CancelLot $cancelLot,
        private readonly BuyLot $buyLot,
        private readonly CreateOrder $createOrder,
        private readonly CancelOrder $cancelOrder,
        private readonly FulfillOrder $fulfillOrder,
        private readonly TakeClaim $takeClaim,
        private readonly SellToShop $sellToShop,
    ) {}

    public function index(int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = Auction::where('structure_id', $auction->id)
            ->with(['item.itemInfo'])
            ->get();

        return view('auction::list', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLot(int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = Auction::where('structure_id', $auction->id)
            ->where('user_id', $user->id)
            ->with(['item.itemInfo'])
            ->get();

        return view('auction::list_my_lot', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLotEdit(int $id, int $slotId): mixed
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $itemEdit = Auction::where('id', $slotId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('auction::edit_lot', compact('auction', 'user', 'itemEdit'));
    }

    public function myLotCancel(int $id, int $slotId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $result = $this->cancelLot->execute($user, $slotId);
        session()->flash('message', $result['message']);

        return redirect()->route('auction.my_lot', ['id' => $auction->id]);
    }

    public function newLot(Request $request, int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
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

        return view('auction::new_lot', compact('auction', 'user', 'itemsToSell', 'selectedItem'));
    }

    public function newLotSave(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'form.item_id'      => 'required|integer',
            'form.amount'       => 'required|integer|min:1',
            'form.price'        => 'required|integer|min:1',
            'form.is_anonymous' => 'nullable|boolean',
        ]);

        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->createLot->execute(
            user: $user,
            auction: $auction,
            itemId: (int) $data['form']['item_id'],
            amount: (int) $data['form']['amount'],
            price: (int) $data['form']['price'],
            isAnonymous: (bool) ($data['form']['is_anonymous'] ?? false),
        );

        session()->flash('message', $result['message']);

        return redirect()->route('auction.new_lot', ['id' => $auction->id]);
    }

    public function buyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->buyLot->execute($user, $auction, $itemId);
        session()->flash('message', $result['message']);

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id): mixed
    {
        $user = Auth::user();
        $shop = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $result = $this->sellToShop->execute($user, (array) $request->input('item', []));
            session()->flash('message', $result['message']);

            if (! $result['ok']) {
                return redirect()->back();
            }
        }

        $itemsToSell = $this->getSellableBackpackItems($user->id);

        return view('shop::sell', compact('shop', 'user', 'itemsToSell'));
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

        return view('auction::exchange', compact('auction', 'user', 'orders', 'filter'));
    }

    public function myOrders(int $id): mixed
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $orders = AuctionOrder::where('structure_id', $auction->id)
            ->where('user_id', $user->id)
            ->with('shareItem')
            ->get();

        return view('auction::my_orders', compact('auction', 'user', 'orders'));
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

        return view('auction::new_order', compact('auction', 'user', 'shareItems', 'selectedItem'));
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

        $result = $this->createOrder->execute(
            user: $user,
            auction: $auction,
            shareItemId: (int) $data['form']['share_item_id'],
            count: (int) $data['form']['count'],
            price: (int) $data['form']['price'],
            isAnonymous: (bool) ($data['form']['is_anonymous'] ?? false),
        );

        session()->flash('message', $result['message']);

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function cancelOrder(int $id, int $orderId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->cancelOrder->execute($user, $orderId);
        session()->flash('message', $result['message']);

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function fulfillOrder(Request $request, int $id, int $orderId): RedirectResponse
    {
        $data = $request->validate(['count' => 'nullable|integer|min:1']);

        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->fulfillOrder->execute($user, $auction, $orderId, (int) ($data['count'] ?? 1));
        session()->flash('message', $result['message']);

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

        return view('auction::claims', compact('auction', 'user', 'claims'));
    }

    public function claimTake(int $id, int $claimId): RedirectResponse
    {
        $user    = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->takeClaim->execute($user, $claimId);
        if (! $result['ok']) {
            session()->flash('message', $result['message']);
        }

        return redirect()->route('auction.claims', ['id' => $auction->id]);
    }

    private function getSellableBackpackItems(int $userId): mixed
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

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        return redirect()->back();
    }
}