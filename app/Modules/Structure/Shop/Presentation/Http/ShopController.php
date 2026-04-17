<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Models\User;
use App\Modules\Structure\Shop\Application\UseCases\BuyItem;
use App\Modules\Structure\Shop\Application\UseCases\GetSellableItems;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function __construct(
        private readonly BuyItem $buyItem,
        private readonly SellItems $sellItems,
        private readonly GetSellableItems $getSellableItems,
    ) {}

    public function index(int $id): mixed
    {
        $user = Auth::user();
        $shop = Structure::with('shopItems.item', 'shopItems.requirements.item')->find($id);

        if (! $shop) {
            abort(404);
        }

        return view('shop::buy', compact('shop', 'user'));
    }

    public function buyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        /** @var User $user */
        $user   = Auth::user();
        $result = $this->buyItem->execute(
            user: $user,
            shareItemId: $itemId,
            count: $request->integer('count', 1),
        );

        session()->flash('message', $result->message);

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();
        $shop = Structure::find($id);

        if (! $shop) {
            abort(404);
        }

        if ($request->isMethod('POST')) {
            $result = $this->sellItems->execute($user, (array) $request->input('item', []));
            session()->flash('message', $result->message);

            if (! $result->ok) {
                return redirect()->back();
            }
        }

        $itemsToSell = $this->getSellableItems->execute($user->id);

        return view('shop::sell', compact('shop', 'user', 'itemsToSell'));
    }
}