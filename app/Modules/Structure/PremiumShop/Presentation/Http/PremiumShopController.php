<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Bank\Infrastructure\Persistence\Models\BankStock;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\PremiumShop\Application\UseCases\GetShopItems;
use App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\PremiumShopItemTooltipStrategy;
use App\Services\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PremiumShopController extends Controller
{
    private const SHOP_ID = 10;

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

    public function topup(): \Illuminate\View\View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('premium_shop::topup', compact('user'));
    }

    public function stock(ItemTooltipCollector $tooltipCollector): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        $stock = BankStock::current();

        $userTotal = 0.0;
        $reachedTierIndex = -1;
        $itemTooltipScript = '';

        if ($stock) {
            $stock->load('tiers.items.shareItem');

            $userTotal = (float) $stock->contributions()
                ->where('user_id', $user->id)
                ->sum('amount');

            foreach ($stock->tiers as $i => $tier) {
                if ($userTotal >= $tier->diamond_threshold) {
                    $reachedTierIndex = $i;
                }
            }

            $shareItems = $stock->tiers
                ->flatMap(fn ($tier) => $tier->items->map->shareItem)
                ->filter()
                ->unique('id');

            $tooltipCollector->collectFrom(new ShareItemTooltipStrategy($shareItems));
            $itemTooltipScript = $tooltipCollector->renderScript();
        }

        return view('premium_shop::stock', [
            'user'              => $user,
            'stock'             => $stock,
            'userTotal'         => $userTotal,
            'reachedTierIndex'  => $reachedTierIndex,
            'itemTooltipScript' => $itemTooltipScript,
        ]);
    }
}
