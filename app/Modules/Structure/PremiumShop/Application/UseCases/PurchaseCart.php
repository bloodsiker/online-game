<?php

declare(strict_types=1);

namespace App\Modules\Structure\PremiumShop\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Commerce\Application\Services\PurchaseLogger;
use App\Modules\Commerce\Domain\Enums\PurchaseSourceType;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\PremiumShop\Application\DTOs\PremiumShopResultDTO;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseCart
{
    public function __construct(
        private readonly ShopCartService $shopCartService,
        private readonly PurchaseLogger $purchaseLogger,
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
                $shareItemId = (int) $shareItem->id;
                $existing = $shareItem->is_stackable
                    ? $existingStacks->get($shareItemId)
                    : null;

                if ($existing instanceof Backpack) {
                    $existing->count += $itemInCart->quantity;
                    $existing->save();
                } else {
                    $copies = $shareItem->is_stackable ? 1 : $itemInCart->quantity;
                    for ($i = 1; $i <= $copies; $i++) {
                        $item = new Item;
                        $item->share_item_id = $shareItem->id;
                        $item->count_use = $shareItem->count_use;
                        $item->save();

                        $createdBackpackItem = Backpack::create([
                            'user_id' => $user->id,
                            'item_id' => $item->id,
                            'count' => $shareItem->is_stackable ? $itemInCart->quantity : 1,
                        ]);
                        if ($shareItem->is_stackable && ! $existingStacks->has($shareItemId)) {
                            $existingStacks->put($shareItemId, $createdBackpackItem);
                        }
                    }
                }
            }

            $user->money -= $cart->getTotalPrice();
            $user->diamond -= $cart->getTotalDiamond();
            $user->save();

            $structure = Structure::query()->findOrFail($shopId);
            $this->purchaseLogger->record(
                user: $user,
                sourceType: PurchaseSourceType::PremiumShop,
                lines: $cart->getItems()->map(static function ($cartItem): array {
                    $requirements = $cartItem->shopItem->relationLoaded('requirements')
                        ? $cartItem->shopItem->requirements->map(static fn ($requirement): array => [
                            'share_item_id' => (int) $requirement->share_item_id,
                            'item_name' => $requirement->item?->name,
                            'quantity' => (int) $requirement->quantity * (int) $cartItem->quantity,
                        ])->values()->all()
                        : [];

                    return [
                        'item' => $cartItem->shopItem->item,
                        'quantity' => (int) $cartItem->quantity,
                        'unit_price' => (int) $cartItem->shopItem->price,
                        'unit_diamond' => (int) $cartItem->shopItem->diamond,
                        'requirements' => $requirements,
                    ];
                }),
                structure: $structure,
                sourceId: $structure->id,
            );

            $this->shopCartService->clearCart($user, $shopId);
        });

        return new PremiumShopResultDTO(true, '');
    }
}
