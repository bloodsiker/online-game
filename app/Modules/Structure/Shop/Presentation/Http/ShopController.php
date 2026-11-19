<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\DTOs\ShopFilterDTO;
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
            'page' => $this->getBuyPage->execute(
                $user,
                $id,
                $request->integer('category_id') ?: null,
                $this->expectedType(),
                $this->filterFromRequest($request, $id),
            ),
        ]);
    }

    /**
     * Фильтр берётся из отправленной формы и запоминается в сессии для этого магазина,
     * поэтому переживает переходы по категориям и редиректы корзины.
     */
    private function filterFromRequest(Request $request, int $shopId): ShopFilterDTO
    {
        $key = 'shop_filter.'.$shopId;

        if ($request->boolean('filter_reset')) {
            $request->session()->forget($key);

            return new ShopFilterDTO;
        }

        if ($request->boolean('filter')) {
            $request->session()->put($key, $request->only([
                'search', 'available', 'level_mode', 'level_from', 'level_to',
                'rarity', 'sort',
            ]));
        }

        $input = collect($request->session()->get($key, []));
        if ($input->isEmpty()) {
            return new ShopFilterDTO;
        }

        $levelMode = (string) $input->get('level_mode', ShopFilterDTO::LEVEL_ANY);
        $sort = (string) $input->get('sort', ShopFilterDTO::SORT_DEFAULT);
        $levelFrom = $input->get('level_from');
        $levelTo = $input->get('level_to');

        return new ShopFilterDTO(
            search: mb_substr(trim((string) $input->get('search', '')), 0, 50),
            availableOnly: (bool) $input->get('available'),
            levelMode: in_array($levelMode, [ShopFilterDTO::LEVEL_ANY, ShopFilterDTO::LEVEL_MINE, ShopFilterDTO::LEVEL_RANGE], true) ? $levelMode : ShopFilterDTO::LEVEL_ANY,
            levelFrom: is_numeric($levelFrom) ? max(0, (int) $levelFrom) : null,
            levelTo: is_numeric($levelTo) ? max(0, (int) $levelTo) : null,
            rarities: array_values(array_intersect(
                (array) $input->get('rarity', []),
                array_map(static fn (ItemRarity $rarity): string => $rarity->value, ItemRarity::cases()),
            )),
            sort: array_key_exists($sort, ShopFilterDTO::SORTS) ? $sort : ShopFilterDTO::SORT_DEFAULT,
        );
    }

    public function addCart(Request $request, int $id): RedirectResponse
    {
        $this->ensureStructureType($id);

        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->addItem(
            $user,
            $request->integer('shop_item_id'),
            $request->integer('quantity', 1),
            $id,
        );

        return redirect()->back();
    }

    public function deleteCart(int $id, int $cartId): RedirectResponse
    {
        $this->ensureStructureType($id);

        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->removeItem($user, $cartId, $id);

        return redirect()->back();
    }

    public function clearCart(int $id): RedirectResponse
    {
        $this->ensureStructureType($id);

        /** @var User $user */
        $user = Auth::user();
        $this->shopCartService->clearCart($user, $id);

        return redirect()->back();
    }

    public function purchase(int $id): RedirectResponse
    {
        $this->ensureStructureType($id);

        /** @var User $user */
        $user = Auth::user();
        $result = $this->purchaseCart->execute($user, $id, $this->expectedType());

        if (! $result->ok) {
            session()->flash('message', $result->message);
        }

        return redirect()->back();
    }

    public function sellItem(Request $request, int $id): mixed
    {
        $shop = $this->ensureStructureType($id, [Structure::TYPE_SHOP, Structure::TYPE_MAGE_TOWER]);

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
            'indexRoute' => $shop->isMageTower() ? 'mage_tower' : 'shop',
        ]);
    }

    protected function expectedType(): string
    {
        return Structure::TYPE_SHOP;
    }

    private function ensureStructureType(int $id, ?array $types = null): Structure
    {
        return Structure::query()
            ->whereKey($id)
            ->whereIn('type', $types ?? [$this->expectedType()])
            ->firstOrFail();
    }
}
