<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Application\UseCases;

use App\Enums\ShareItemType;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\PremiumShop\Application\DTOs\PremiumShopResultDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use Illuminate\Support\Facades\DB;

class PurchaseCart
{
    public function __construct(
        private readonly ShopCartService $shopCartService,
    ) {}

    public function execute(User $user, int $shopId): PremiumShopResultDTO
    {
        $cart = $this->shopCartService->getCart($user, $shopId);

        if ($cart->getTotalDiamond() && $user->diamond < $cart->getTotalDiamond()) {
            return new PremiumShopResultDTO(false, 'У Вас недостаточно денег, чтобы оплатить заказ!');
        }

        if ($cart->getTotalPrice() && $user->money < $cart->getTotalPrice()) {
            return new PremiumShopResultDTO(false, 'У Вас недостаточно денег, чтобы оплатить заказ!');
        }

        DB::transaction(function () use ($user, $cart, $shopId) {
            foreach ($cart->getItems() as $itemInCart) {
                $shareItem = $itemInCart->shopItem->item;

                $existing = Backpack::select('backpacks.*')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('items.share_item_id', $shareItem->id)
                    ->first();

                if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
                    $existing->count += $itemInCart->quantity;
                    $existing->save();
                } else {
                    for ($i = 1; $i <= $itemInCart->quantity; $i++) {
                        $item = new Item;
                        $item->share_item_id = $shareItem->id;
                        $item->count_use = $shareItem->count_use;
                        $item->save();

                        $user->backpack()->attach($item->id, ['equipped' => 0, 'count' => 1]);
                    }
                }
            }

            $user->money -= $cart->getTotalPrice();
            $user->diamond -= $cart->getTotalDiamond();
            $user->save();

            $this->shopCartService->clearCart($user, $shopId);
        });

        return new PremiumShopResultDTO(true, '');
    }
}
