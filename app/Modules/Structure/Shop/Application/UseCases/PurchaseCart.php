<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseCart
{
    public function __construct(
        private readonly ShopCartService $shopCartService,
    ) {}

    public function execute(User $user, int $shopId): ShopResultDTO
    {
        $cart = $this->shopCartService->getCart($user, $shopId);

        if ($cart->getItems()->isEmpty()) {
            return new ShopResultDTO(false, 'Корзина пуста.');
        }

        if ($cart->getTotalDiamond() && $user->diamond < $cart->getTotalDiamond()) {
            return new ShopResultDTO(false, 'У Вас недостаточно денег, чтобы оплатить заказ!');
        }

        if ($cart->getTotalPrice() && $user->money < $cart->getTotalPrice()) {
            return new ShopResultDTO(false, 'У Вас недостаточно денег, чтобы оплатить заказ!');
        }

        DB::transaction(function () use ($user, $cart, $shopId): void {
            $stackableShareItemIds = $cart->getItems()
                ->map(static fn ($itemInCart) => $itemInCart->shopItem->item)
                ->filter(static fn ($shareItem): bool => $shareItem->is_stackable)
                ->pluck('id')
                ->map(static fn (mixed $shareItemId): int => (int) $shareItemId)
                ->unique()
                ->values()
                ->all();
            $existingStacks = $stackableShareItemIds === []
                ? collect()
                : Backpack::query()
                    ->select('backpacks.*')
                    ->addSelect('items.share_item_id as stack_share_item_id')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('backpacks.user_id', $user->id)
                    ->whereIn('items.share_item_id', $stackableShareItemIds)
                    ->get()
                    ->keyBy('stack_share_item_id');

            foreach ($cart->getItems() as $itemInCart) {
                $shareItem = $itemInCart->shopItem->item;

                if (! $shareItem->is_stackable) {
                    for ($i = 1; $i <= $itemInCart->quantity; $i++) {
                        $item = new Item;
                        $item->share_item_id = $shareItem->id;
                        $item->count_use = $shareItem->count_use;
                        $item->save();

                        $user->backpack()->attach($item->id, ['equipped' => 0, 'count' => 1]);
                    }

                    continue;
                }

                $shareItemId = (int) $shareItem->id;
                $existing = $existingStacks->get($shareItemId);

                if ($existing instanceof Backpack) {
                    $existing->count += $itemInCart->quantity;
                    $existing->save();
                } else {
                    $item = new Item;
                    $item->share_item_id = $shareItem->id;
                    $item->count_use = $shareItem->count_use;
                    $item->save();

                    $createdBackpackItem = Backpack::create([
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'count' => $itemInCart->quantity,
                    ]);
                    $existingStacks->put($shareItemId, $createdBackpackItem);
                }
            }

            $user->money -= $cart->getTotalPrice();
            $user->diamond -= $cart->getTotalDiamond();
            $user->save();

            $this->shopCartService->clearCart($user, $shopId);
        });

        return new ShopResultDTO(true, '');
    }
}
