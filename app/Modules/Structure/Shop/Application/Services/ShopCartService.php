<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Services;

use App\Modules\Structure\Shop\Application\DTOs\ShopCartDTO;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopCart;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class ShopCartService
{
    public function addItem(User $user, int $shopItemId, int $quantity = 1, ?int $structureId = null): ShopCart
    {
        ShopItem::query()
            ->whereKey($shopItemId)
            ->when($structureId !== null, fn ($query) => $query->where('structure_id', $structureId))
            ->firstOrFail();
        $quantity = max(1, $quantity);

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

    public function removeItem(User $user, int $cartId, ?int $structureId = null): bool
    {
        return ShopCart::where('user_id', $user->id)
            ->where('id', $cartId)
            ->when(
                $structureId !== null,
                fn ($query) => $query->whereHas(
                    'shopItem',
                    fn ($shopItemQuery) => $shopItemQuery->where('structure_id', $structureId),
                ),
            )
            ->delete() > 0;
    }

    public function getCart(User $user, int $structureId): ShopCartDTO
    {
        $cartItems = ShopCart::with(['shopItem', 'shopItem.item', 'shopItem.requirements.item'])
            ->join('shop_items', 'shop_items.id', '=', 'shop_carts.shop_item_id')
            ->where('shop_items.structure_id', $structureId)
            ->where('shop_carts.user_id', $user->id)
            ->select('shop_carts.*')
            ->get();

        $totalPrice = $cartItems->sum(fn ($item) => $item->quantity * $item->shopItem->price);
        $totalDiamond = $cartItems->sum(fn ($item) => $item->quantity * $item->shopItem->diamond);

        $requirementTotals = $cartItems
            ->flatMap(fn ($cartItem) => $cartItem->shopItem->requirements->map(fn ($requirement): array => [
                'item' => $requirement->item,
                'quantity' => (int) $requirement->quantity * (int) $cartItem->quantity,
            ]))
            ->filter(fn (array $requirement): bool => $requirement['item'] !== null)
            ->groupBy(fn (array $requirement): int => (int) $requirement['item']->id)
            ->map(fn ($requirements): array => [
                'item' => $requirements->first()['item'],
                'quantity' => $requirements->sum('quantity'),
            ])
            ->values()
            ->all();

        return new ShopCartDTO(
            items: $cartItems,
            totalDiamond: $totalDiamond,
            totalPrice: $totalPrice,
            requirementTotals: $requirementTotals,
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
