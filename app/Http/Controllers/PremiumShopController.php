<?php

namespace App\Http\Controllers;

use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Shop\ShopItem;
use App\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\PremiumShopItemTooltipStrategy;
use App\Services\ShopCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PremiumShopController extends Controller
{
    public function __construct(
        protected readonly ShopCartService $shopCartService,
        protected readonly ItemTooltipCollector $collector,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        $premiumShopId = 8;

        $shop = Structure::with('shopItems.item', 'shopItems.requirements.item')->find($premiumShopId);
        $firstCategory = $shop->categories()->first();
        $activeCategoryId = $request->integer('category_id', $firstCategory->id);

        $items = ShopItem::select('shop_items.*')
            ->with(['item', 'requirements.item'])
            ->where('shop_items.structure_id', $shop->id)
            ->where('share_structure_category_id', $activeCategoryId)
            ->orderBy('shop_items.sort_order', 'desc')->get();

        $cart = $this->shopCartService->getCart($user, $shop->id);

        $this->collector->collectFrom(new PremiumShopItemTooltipStrategy($items));

        $itemTooltipScript = $this->collector->renderScript();

        return view('premium_shop.buy', compact('user', 'shop', 'activeCategoryId', 'items', 'cart', 'itemTooltipScript'));
    }

    public function buy(Request $request)
    {
        $user = Auth::user();

        $premiumShopId = 7;

        $cart = $this->shopCartService->getCart($user, $premiumShopId);

        if ($cart->getTotalDiamond() && $user->diamond < $cart->getTotalDiamond()) {
            session()->flash('message', 'У Вас недостаточно денег, чтобы оплатить заказ!');

            return redirect()->back();
        }

        if ($cart->getTotalPrice() && $user->money < $cart->getTotalPrice()) {
            session()->flash('message', 'У Вас недостаточно денег, чтобы оплатить заказ!');

            return redirect()->back();
        }

        DB::transaction(function () use ($user, $cart, $premiumShopId) {
            foreach ($cart->getItems() as $itemInCart) {
                $shareItem = $itemInCart->shopItem->item;

                $hasBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $shareItem->id])
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->first();

                if ($hasBackpack instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                    $hasBackpack->count += $itemInCart->quantity;
                    $hasBackpack->save();
                } else {
                    for ($i = 1; $i <= $itemInCart->quantity; $i++) {
                        $item = new Item;
                        $item->share_item_id = $shareItem->id;
                        $item->count_use = $shareItem->count_use;
                        $item->save();

                        $user->backpack()->attach($item->id, [
                            'equipped' => 0,
                            'count' => 1,
                        ]);
                    }
                }
            }

            $user->money -= $cart->getTotalPrice();
            $user->diamond -= $cart->getTotalDiamond();
            $user->save();

            $this->shopCartService->clearCart($user, $premiumShopId);
        });

        return redirect()->back();
    }

    public function addCart(Request $request)
    {
        $user = Auth::user();

        $this->shopCartService->addItem($user, $request->integer('shop_item_id'), $request->integer('quantity'));

        return redirect()->back();
    }

    public function deleteCart(int $id)
    {
        $user = Auth::user();

        $this->shopCartService->removeItem($user, $id);

        return redirect()->back();
    }

    public function clearCart()
    {
        $user = Auth::user();
        $premiumShopId = 7;

        $this->shopCartService->clearCart($user, $premiumShopId);

        return redirect()->back();
    }
}
