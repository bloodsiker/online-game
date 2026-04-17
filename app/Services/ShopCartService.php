<?php

namespace App\Services;

use App\DTO\ShopCartDTO;
use App\Models\Shop\ShopCart;
use App\Models\Shop\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class ShopCartService
{
    public function addItem(User $user, int $shopItemId, int $quantity = 1): ShopCart
    {
        $shopItem = ShopItem::where('id', $shopItemId)
//            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($user, $shopItemId, $quantity) {
            $cartItem = ShopCart::where('user_id', $user->id)
                ->where('shop_item_id', $shopItemId)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantity);
                $cartItem->refresh();
            } else {
                $cartItem = ShopCart::create([
                    'user_id' => $user->id,
                    'shop_item_id' => $shopItemId,
                    'quantity' => $quantity,
                ]);
            }

            return $cartItem->load('shopItem');
        });
    }

    public function updateQuantity(User $user, int $shopItemId, int $quantity): ?ShopCart
    {
        $cartItem = ShopCart::where('user_id', $user->id)
            ->where('shop_item_id', $shopItemId)
            ->firstOrFail();

        return DB::transaction(function () use ($cartItem, $quantity) {
            if ($quantity <= 0) {
                $cartItem->delete();

                return null;
            }

            $cartItem->update(['quantity' => $quantity]);

            return $cartItem->refresh()->load('shopItem');
        });
    }

    public function removeItem(User $user, int $shopItemId): bool
    {
        return ShopCart::where('user_id', $user->id)
            ->where('id', $shopItemId)
            ->delete() > 0;
    }

    public function getCart(User $user, int $structureId): ShopCartDTO
    {
        $cartItems = ShopCart::with(['shopItem', 'shopItem.item'])
            ->join('shop_items', 'shop_items.id', '=', 'shop_carts.shop_item_id')
            ->where('shop_items.structure_id', $structureId)
            ->where('shop_carts.user_id', $user->id)
            ->select('shop_carts.*')
            ->get();

        $totalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * $item->shopItem->price;
        });

        $totalDiamond = $cartItems->sum(function ($item) {
            return $item->quantity * $item->shopItem->diamond;
        });

        return new ShopCartDTO(
            items: $cartItems,
            totalDiamond: $totalDiamond,
            totalPrice: $totalPrice,
        );
    }

    public function clearCart(User $user, int $structureId): int
    {
        return ShopCart::where('user_id', $user->id)
            ->join('shop_items', 'shop_items.id', '=', 'shop_carts.shop_item_id')
            ->where('shop_items.structure_id', $structureId)
            ->delete();
    }

    public function getTotalPrice(User $user, int $structureId): float
    {
        return $this->getCart($user, $structureId)->getTotalPrice();
    }

    public function getTotalDiamond(User $user, int $structureId): float
    {
        return $this->getCart($user, $structureId)->getTotalDiamond();
    }
}
