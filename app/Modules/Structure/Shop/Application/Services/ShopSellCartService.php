<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellCartDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellCartLineDTO;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopCart;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Корзина на продажу: предметы из сумки игрока, отложенные для продажи в конкретный магазин.
 * Хранится в той же таблице shop_carts, что и корзина покупки, с type = sell.
 */
class ShopSellCartService
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly SellItems $sellItems,
    ) {}

    public function add(User $user, int $structureId, int $itemId, int $quantity): ShopResultDTO
    {
        $backpackItem = $this->readRepository
            ->getSelectedSellableItems(userId: (int) $user->id, itemIds: [$itemId])
            ->first();

        if ($backpackItem === null) {
            return new ShopResultDTO(false, 'Этот предмет нельзя продать.');
        }

        $available = (int) $backpackItem->count;

        return DB::transaction(function () use ($user, $structureId, $itemId, $quantity, $available): ShopResultDTO {
            User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $line = ShopCart::selling()
                ->where('user_id', $user->id)
                ->where('structure_id', $structureId)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            $total = min($available, max(1, $quantity) + (int) ($line?->quantity ?? 0));

            if ($line !== null) {
                $line->update(['quantity' => $total]);
            } else {
                ShopCart::create([
                    'user_id' => $user->id,
                    'type' => ShopCart::TYPE_SELL,
                    'structure_id' => $structureId,
                    'item_id' => $itemId,
                    'quantity' => $total,
                ]);
            }

            return new ShopResultDTO(true, 'Предмет добавлен в корзину на продажу.');
        }, attempts: 3);
    }

    /**
     * Новое количество строки корзины (не больше, чем есть в сумке).
     *
     * @return array{quantity: int, available: int, unit_price: int}|null null — строки нет или предмет больше нельзя продать
     */
    public function updateQuantity(User $user, int $structureId, int $itemId, int $quantity): ?array
    {
        $backpackItem = $this->readRepository
            ->getSelectedSellableItems(userId: (int) $user->id, itemIds: [$itemId])
            ->first();

        if ($backpackItem === null) {
            return null;
        }

        $available = (int) $backpackItem->count;
        $quantity = min($available, max(1, $quantity));

        $updated = ShopCart::selling()
            ->where('user_id', $user->id)
            ->where('structure_id', $structureId)
            ->where('item_id', $itemId)
            ->update(['quantity' => $quantity, 'updated_at' => now()]);

        if ($updated === 0) {
            return null;
        }

        return [
            'quantity' => $quantity,
            'available' => $available,
            'unit_price' => max(0, (int) round($backpackItem->item->itemInfo->price / 2)),
        ];
    }

    public function remove(User $user, int $structureId, int $cartId): bool
    {
        return ShopCart::selling()
            ->where('user_id', $user->id)
            ->where('structure_id', $structureId)
            ->whereKey($cartId)
            ->delete() > 0;
    }

    public function clear(User $user, int $structureId): int
    {
        return ShopCart::selling()
            ->where('user_id', $user->id)
            ->where('structure_id', $structureId)
            ->delete();
    }

    /**
     * Строки корзины, сверенные с сумкой: проданные/надетые предметы не показываются,
     * количество не больше, чем есть в сумке.
     *
     * @param  Collection<int, Backpack>  $sellableItems
     */
    public function getCart(User $user, int $structureId, Collection $sellableItems): ShopSellCartDTO
    {
        $backpackByItem = $sellableItems->keyBy(static fn (Backpack $item): int => (int) $item->item_id);
        $lines = [];

        $rows = ShopCart::selling()
            ->where('user_id', $user->id)
            ->where('structure_id', $structureId)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            /** @var Backpack|null $backpackItem */
            $backpackItem = $backpackByItem->get((int) $row->item_id);
            if ($backpackItem === null) {
                continue;
            }

            $info = $backpackItem->item->itemInfo;
            $lines[] = new ShopSellCartLineDTO(
                cartId: (int) $row->id,
                itemId: (int) $row->item_id,
                name: (string) $info->name,
                color: $info->rarity?->color() ?? '#666666',
                quantity: min((int) $row->quantity, (int) $backpackItem->count),
                unitPrice: max(0, (int) round($info->price / 2)),
                infoUrl: route('items.info', ['id' => $backpackItem->item->id]),
            );
        }

        return new ShopSellCartDTO($lines);
    }

    /** Продать всё из корзины одной операцией; после успешной продажи корзина очищается. */
    public function checkout(User $user, int $structureId): ShopResultDTO
    {
        $rows = ShopCart::selling()
            ->where('user_id', $user->id)
            ->where('structure_id', $structureId)
            ->get();

        if ($rows->isEmpty()) {
            return new ShopResultDTO(false, 'Корзина на продажу пуста.');
        }

        $checkedItems = $rows
            ->mapWithKeys(static fn (ShopCart $row): array => [
                (int) $row->item_id => ['selected' => 1, 'count' => (int) $row->quantity],
            ])
            ->all();

        $result = $this->sellItems->execute($user, $checkedItems);
        if ($result->ok) {
            $this->clear($user, $structureId);
        }

        return $result;
    }
}
