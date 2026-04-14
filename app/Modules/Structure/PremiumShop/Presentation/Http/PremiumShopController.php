<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Models\Shop\ShopItem;
use App\Models\Structure;
use App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart;
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
    ) {}

    public function index(Request $request): \Illuminate\View\View
    {
        $user = Auth::user();
        $shop = Structure::with('shopItems.item', 'shopItems.requirements.item')->find(self::SHOP_ID);

        $firstCategory   = $shop->categories()->first();
        $activeCategoryId = $request->integer('category_id', $firstCategory->id);

        $items = ShopItem::select('shop_items.*')
            ->with(['item', 'requirements.item'])
            ->where('shop_items.structure_id', $shop->id)
            ->where('share_structure_category_id', $activeCategoryId)
            ->orderBy('shop_items.sort_order', 'desc')
            ->get();

        $cart = $this->shopCartService->getCart($user, $shop->id);

        $this->collector->collectFrom(new PremiumShopItemTooltipStrategy($items));
        $itemTooltipScript = $this->collector->renderScript();

        return view('premium_shop::buy', compact('user', 'shop', 'activeCategoryId', 'items', 'cart', 'itemTooltipScript'));
    }

    public function buy(Request $request): RedirectResponse
    {
        $result = $this->purchaseCart->execute(Auth::user(), self::SHOP_ID);

        if (! $result['ok']) {
            session()->flash('message', $result['message']);
        }

        return redirect()->back();
    }

    public function addCart(Request $request): RedirectResponse
    {
        $this->shopCartService->addItem(
            Auth::user(),
            $request->integer('shop_item_id'),
            $request->integer('quantity'),
        );

        return redirect()->back();
    }

    public function deleteCart(int $id): RedirectResponse
    {
        $this->shopCartService->removeItem(Auth::user(), $id);

        return redirect()->back();
    }

    public function clearCart(): RedirectResponse
    {
        $this->shopCartService->clearCart(Auth::user(), self::SHOP_ID);

        return redirect()->back();
    }
}