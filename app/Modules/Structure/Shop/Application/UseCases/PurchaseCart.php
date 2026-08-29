<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseCart
{
    public function __construct(
        private readonly ShopCartService $shopCartService,
    ) {}

    public function execute(User $user, int $shopId, ?string $expectedType = null): ShopResultDTO
    {
        if ($expectedType !== null) {
            Structure::query()
                ->whereKey($shopId)
                ->where('type', $expectedType)
                ->firstOrFail();
        }

        return DB::transaction(function () use ($user, $shopId): ShopResultDTO {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $cart = $this->shopCartService->getCart($lockedUser, $shopId);

            if ($cart->getItems()->isEmpty()) {
                return new ShopResultDTO(false, 'Корзина пуста.');
            }

            if ($cart->getTotalDiamond() > 0 && $lockedUser->diamond < $cart->getTotalDiamond()) {
                return new ShopResultDTO(false, 'Недостаточно алмазов для оплаты заказа.');
            }

            if ($cart->getTotalPrice() > 0 && $lockedUser->money < $cart->getTotalPrice()) {
                return new ShopResultDTO(false, 'Недостаточно монет для оплаты заказа.');
            }

            $requirementTotals = collect($cart->getRequirementTotals())
                ->keyBy(fn (array $requirement): int => (int) $requirement['item']->id);
            $requirementBackpackItems = collect();

            if ($requirementTotals->isNotEmpty()) {
                $requirementBackpackItems = Backpack::query()
                    ->select('backpacks.*')
                    ->addSelect('items.share_item_id as payment_share_item_id')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('backpacks.user_id', $lockedUser->id)
                    ->where('backpacks.equipped', 0)
                    ->whereIn('items.share_item_id', $requirementTotals->keys())
                    ->orderBy('backpacks.id')
                    ->lockForUpdate()
                    ->get();

                $availableCounts = $requirementBackpackItems
                    ->groupBy(fn (Backpack $item): int => (int) $item->payment_share_item_id)
                    ->map(fn ($items): int => (int) $items->sum('count'));

                foreach ($requirementTotals as $shareItemId => $requirement) {
                    if (($availableCounts[$shareItemId] ?? 0) < $requirement['quantity']) {
                        return new ShopResultDTO(
                            false,
                            sprintf(
                                'Недостаточно предметов «%s»: нужно %d.',
                                $requirement['item']->name,
                                $requirement['quantity'],
                            ),
                        );
                    }
                }
            }

            $this->consumeRequirements($requirementBackpackItems, $requirementTotals);

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
                    ->where('backpacks.user_id', $lockedUser->id)
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

                        $lockedUser->backpack()->attach($item->id, ['equipped' => 0, 'count' => 1]);
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
                        'user_id' => $lockedUser->id,
                        'item_id' => $item->id,
                        'count' => $itemInCart->quantity,
                    ]);
                    $existingStacks->put($shareItemId, $createdBackpackItem);
                }
            }

            $lockedUser->money -= $cart->getTotalPrice();
            $lockedUser->diamond -= $cart->getTotalDiamond();
            $lockedUser->save();

            $this->shopCartService->clearCart($lockedUser, $shopId);

            return new ShopResultDTO(true, 'Товары куплены.');
        });
    }

    private function consumeRequirements($backpackItems, $requirements): void
    {
        foreach ($requirements as $shareItemId => $requirement) {
            $remaining = (int) $requirement['quantity'];
            $items = $backpackItems
                ->where('payment_share_item_id', $shareItemId)
                ->values();

            foreach ($items as $backpackItem) {
                if ($remaining <= 0) {
                    break;
                }

                $consumed = min($remaining, (int) $backpackItem->count);
                $remaining -= $consumed;

                if ($consumed < $backpackItem->count) {
                    $backpackItem->decrement('count', $consumed);

                    continue;
                }

                $itemId = (int) $backpackItem->item_id;
                $backpackItem->delete();

                if (! Backpack::query()->where('item_id', $itemId)->exists()) {
                    Item::query()->whereKey($itemId)->delete();
                }
            }
        }
    }
}
