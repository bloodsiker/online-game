<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Application\UseCases\GetBuyPage;
use App\Modules\Structure\Shop\Application\UseCases\GetSellPage;
use App\Modules\Structure\Shop\Application\UseCases\PurchaseCart;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function __construct(
        private readonly SellItems $sellItems,
        private readonly GetBuyPage $getBuyPage,
        private readonly GetSellPage $getSellPage,
        private readonly ShopCartService $shopCartService,
        private readonly PurchaseCart $purchaseCart,
    ) {}

    public function index(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('shop::buy', [
            'page' => $this->getBuyPage->execute($user, $id, $request->integer('category_id') ?: null),
        ]);
    }

    public function addCart(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->addItem(
            $user,
            $request->integer('shop_item_id'),
            $request->integer('quantity', 1),
        );

        return redirect()->back();
    }

    public function deleteCart(int $id, int $cartId): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->removeItem($user, $cartId);

        return redirect()->back();
    }

    public function clearCart(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->clearCart($user, $id);

        return redirect()->back();
    }

    public function purchase(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->purchaseCart->execute($user, $id);

        if (! $result->ok) {
            session()->flash('message', $result->message);
        }

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        if ($request->isMethod('POST')) {
            $result = $this->sellItems->execute($user, (array) $request->input('item', []));
            session()->flash('message', $result->message);

            if (! $result->ok) {
                return redirect()->back();
            }
        }

        return view('shop::sell', [
            'page' => $this->getSellPage->execute($user, $id),
        ]);
    }
}
