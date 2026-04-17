<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\Structure;
use App\Modules\Structure\PremiumShop\Application\UseCases\GetShopItems;
use App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\PremiumShopItemTooltipStrategy;
use App\Services\ShopCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PremiumShopController extends Controller
{
    private const SHOP_ID = 8;

    public function __construct(
        private readonly ShopCartService $shopCartService,
        private readonly ItemTooltipCollector $collector,
        private readonly PurchaseCart $purchaseCart,
        private readonly GetShopItems $getShopItems,
    ) {}

    public function index(Request $request): \Illuminate\View\View
    {
        /** @var User $user */
        $user = Auth::user();
        $shop = Structure::with('shopItems.item', 'shopItems.requirements.item')->find(self::SHOP_ID);

        $firstCategory = $shop->categories()->first();
        $activeCategoryId = $request->integer('category_id', $firstCategory->id);

        $items = $this->getShopItems->execute($shop->id, $activeCategoryId);
        $cart = $this->shopCartService->getCart($user, $shop->id);

        $this->collector->collectFrom(new PremiumShopItemTooltipStrategy($items));
        $itemTooltipScript = $this->collector->renderScript();

        return view('premium_shop::buy', compact('user', 'shop', 'activeCategoryId', 'items', 'cart', 'itemTooltipScript'));
    }

    public function buy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->purchaseCart->execute($user, self::SHOP_ID);

        if (! $result->ok) {
            session()->flash('message', $result->message);
        }

        return redirect()->back();
    }

    public function addCart(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->addItem(
            $user,
            $request->integer('shop_item_id'),
            $request->integer('quantity'),
        );

        return redirect()->back();
    }

    public function deleteCart(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->removeItem($user, $id);

        return redirect()->back();
    }

    public function clearCart(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->clearCart($user, self::SHOP_ID);

        return redirect()->back();
    }
}
