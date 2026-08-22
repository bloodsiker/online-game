<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Application\Services;

use App\Modules\Reputation\Application\DTOs\ReputationShopCartDTO;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopCart;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class ReputationShopCartService
{
    /** Добавить товар репутации в корзину (складываем количество). */
    public function addItem(User $user, int $reputationShopItemId, int $quantity = 1): ReputationShopCart
    {
        ReputationShopItem::where('id', $reputationShopItemId)->firstOrFail();
        $quantity = max(1, $quantity);

        return DB::transaction(function () use ($user, $reputationShopItemId, $quantity) {
            $cartItem = ReputationShopCart::where('user_id', $user->id)
                ->where('reputation_shop_item_id', $reputationShopItemId)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantity);
                $cartItem->refresh();
            } else {
                $cartItem = ReputationShopCart::create([
                    'user_id' => $user->id,
                    'reputation_shop_item_id' => $reputationShopItemId,
                    'quantity' => $quantity,
                ]);
            }

            return $cartItem->load('shopItem');
        });
    }

    /** Удалить одну строку корзины (по её id). */
    public function removeItem(User $user, int $cartId): bool
    {
        return ReputationShopCart::where('user_id', $user->id)
            ->where('id', $cartId)
            ->delete() > 0;
    }

    /** Корзина только по товарам данной репутации. */
    public function getCart(User $user, int $reputationId): ReputationShopCartDTO
    {
        $cartItems = ReputationShopCart::with(['shopItem', 'shopItem.item', 'shopItem.requirements.item'])
            ->join('reputation_shop_items', 'reputation_shop_items.id', '=', 'reputation_shop_carts.reputation_shop_item_id')
            ->where('reputation_shop_items.reputation_id', $reputationId)
            ->where('reputation_shop_carts.user_id', $user->id)
            ->select('reputation_shop_carts.*')
            ->get();

        $totalPrice = $cartItems->sum(fn ($item) => $item->quantity * $item->shopItem->price);
        $totalDiamond = $cartItems->sum(fn ($item) => $item->quantity * $item->shopItem->diamond);

        return new ReputationShopCartDTO(items: $cartItems, totalDiamond: $totalDiamond, totalPrice: $totalPrice);
    }

    /** Очистить корзину только для данной репутации. */
    public function clearCart(User $user, int $reputationId): int
    {
        return ReputationShopCart::where('reputation_shop_carts.user_id', $user->id)
            ->join('reputation_shop_items', 'reputation_shop_items.id', '=', 'reputation_shop_carts.reputation_shop_item_id')
            ->where('reputation_shop_items.reputation_id', $reputationId)
            ->delete();
    }
}
