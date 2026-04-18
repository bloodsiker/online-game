<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\Share\ShareItem;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Auction\Application\DTOs\ExchangeFilterDTO;
use App\Modules\Structure\Auction\Application\UseCases\BuyLot;
use App\Modules\Structure\Auction\Application\UseCases\CancelLot;
use App\Modules\Structure\Auction\Application\UseCases\CancelOrder;
use App\Modules\Structure\Auction\Application\UseCases\CreateLot;
use App\Modules\Structure\Auction\Application\UseCases\CreateOrder;
use App\Modules\Structure\Auction\Application\UseCases\FulfillOrder;
use App\Modules\Structure\Auction\Application\UseCases\GetAuctionLots;
use App\Modules\Structure\Auction\Application\UseCases\GetClaims;
use App\Modules\Structure\Auction\Application\UseCases\GetExchangeOrders;
use App\Modules\Structure\Auction\Application\UseCases\GetLotForEdit;
use App\Modules\Structure\Auction\Application\UseCases\GetMyOrders;
use App\Modules\Structure\Auction\Application\UseCases\GetSellableItems;
use App\Modules\Structure\Auction\Application\UseCases\SellToShop;
use App\Modules\Structure\Auction\Application\UseCases\TakeClaim;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
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
        private readonly GetAuctionLots $getAuctionLots,
        private readonly GetLotForEdit $getLotForEdit,
        private readonly GetExchangeOrders $getExchangeOrders,
        private readonly GetMyOrders $getMyOrders,
        private readonly GetClaims $getClaims,
        private readonly GetSellableItems $getSellableItems,
    ) {}

    public function index(int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = $this->getAuctionLots->execute($auction->id);

        return view('auction::list', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLot(int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $auctionSlots = $this->getAuctionLots->execute($auction->id, $user->id);

        return view('auction::list_my_lot', compact('auction', 'user', 'auctionSlots'));
    }

    public function myLotEdit(int $id, int $slotId): mixed
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $itemEdit = $this->getLotForEdit->execute($slotId, $user->id);

        return view('auction::edit_lot', compact('auction', 'user', 'itemEdit'));
    }

    public function myLotCancel(int $id, int $slotId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::find($id);

        if (! $auction) {
            return $this->redirectWithMessage('Построение не найдено.');
        }

        $result = $this->cancelLot->execute($user, $slotId);
        session()->flash('message', $result->message);

        return redirect()->route('auction.my_lot', ['id' => $auction->id]);
    }

    public function newLot(Request $request, int $id): mixed
    {
        $user = Auth::user();
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

        $itemsToSell = $this->getSellableItems->execute($user->id);

        return view('auction::new_lot', compact('auction', 'user', 'itemsToSell', 'selectedItem'));
    }

    public function newLotSave(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'form.item_id' => 'required|integer',
            'form.amount' => 'required|integer|min:1',
            'form.price' => 'required|integer|min:1',
            'form.is_anonymous' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->createLot->execute(
            user: $user,
            auction: $auction,
            itemId: (int) $data['form']['item_id'],
            amount: (int) $data['form']['amount'],
            price: (int) $data['form']['price'],
            isAnonymous: (bool) ($data['form']['is_anonymous'] ?? false),
        );

        session()->flash('message', $result->message);

        return redirect()->route('auction.new_lot', ['id' => $auction->id]);
    }

    public function buyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->buyLot->execute($user, $auction, $itemId);
        session()->flash('message', $result->message);

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id): mixed
    {
        $user = Auth::user();
        $shop = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $result = $this->sellToShop->execute($user, (array) $request->input('item', []));
            session()->flash('message', $result->message);

            if (! $result->ok) {
                return redirect()->back();
            }
        }

        $itemsToSell = $this->getSellableItems->execute($user->id);

        return view('shop::sell', compact('shop', 'user', 'itemsToSell'));
    }

    public function exchange(Request $request, int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $filter = new ExchangeFilterDTO(
            matching: $request->input('filter.matching', '1') === '1',
            name: $request->filled('filter.name') ? $request->input('filter.name') : null,
            type: $request->filled('filter.type') ? $request->input('filter.type') : null,
            countMin: $request->filled('filter.count_min') ? (int) $request->input('filter.count_min') : null,
            countMax: $request->filled('filter.count_max') ? (int) $request->input('filter.count_max') : null,
        );

        $orders = $this->getExchangeOrders->execute($auction->id, $user->id, $filter);
        $filterRaw = $request->input('filter', []);

        return view('auction::exchange', [
            'auction' => $auction,
            'user' => $user,
            'orders' => $orders,
            'filter' => $filterRaw,
        ]);
    }

    public function myOrders(int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $orders = $this->getMyOrders->execute($auction->id, $user->id);

        return view('auction::my_orders', compact('auction', 'user', 'orders'));
    }

    public function newOrder(Request $request, int $id): mixed
    {
        $user = Auth::user();
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
            'form.count' => 'required|integer|min:1',
            'form.price' => 'required|integer|min:1',
            'form.is_anonymous' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->createOrder->execute(
            user: $user,
            auction: $auction,
            shareItemId: (int) $data['form']['share_item_id'],
            count: (int) $data['form']['count'],
            price: (int) $data['form']['price'],
            isAnonymous: (bool) ($data['form']['is_anonymous'] ?? false),
        );

        session()->flash('message', $result->message);

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function cancelOrder(int $id, int $orderId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->cancelOrder->execute($user, $orderId);
        session()->flash('message', $result->message);

        return redirect()->route('auction.my_orders', ['id' => $auction->id]);
    }

    public function fulfillOrder(Request $request, int $id, int $orderId): RedirectResponse
    {
        $data = $request->validate(['count' => 'nullable|integer|min:1']);

        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->fulfillOrder->execute($user, $auction, $orderId, (int) ($data['count'] ?? 1));
        session()->flash('message', $result->message);

        return redirect()->route('auction.exchange', ['id' => $auction->id]);
    }

    public function claims(int $id): mixed
    {
        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $claims = $this->getClaims->execute($auction->id, $user->id);

        return view('auction::claims', compact('auction', 'user', 'claims'));
    }

    public function claimTake(int $id, int $claimId): RedirectResponse
    {
        $user = Auth::user();
        $auction = Structure::findOrFail($id);

        $result = $this->takeClaim->execute($user, $claimId);
        if (! $result->ok) {
            session()->flash('message', $result->message);
        }

        return redirect()->route('auction.claims', ['id' => $auction->id]);
    }

    private function redirectWithMessage(string $message): RedirectResponse
    {
        session()->flash('message', $message);

        return redirect()->back();
    }
}
