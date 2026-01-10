<?php

namespace App\Services;

use App\DTO\ExchangeItemDTO;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\ShareItem;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Support\Collection;

readonly class ExchangeItemService
{
    public function __construct(protected BackpackService $backpackService)
    {
    }

    /**
     * @throws \Exception
     */
    public function performExchange(User $user, Structure $exchange, int $fromShareId, int $toShareId, int $count)
    {
        if ($count <= 0) {
            throw new \Exception('Неверное количество для обмена.');
        }

        $fromShareItem = ShareItem::find($fromShareId);
        $backpackFromItem = $this->backpackService->getItem($user, $fromShareItem);
        if (!$backpackFromItem instanceof Backpack) {
            throw new \Exception('У вас нет необходимого предмета для обмена.');
        }

        if ($backpackFromItem->count < $count) {
            throw new \Exception('У вас недостаточно предметов для обмена.');
        }

        $toShareItem = ShareItem::find($toShareId);

        $exchangeItem = $exchange->exchangeItems()
            ->where('from_share_item_id', $fromShareItem->id)
            ->where('to_share_item_id', $toShareItem->id)
            ->first();

        $exchangeRate = $exchangeItem->to_amount / $exchangeItem->from_amount;
        $amountToExchange = $count * $exchangeRate;

        \DB::transaction(function () use ($user, $exchange, $count, $amountToExchange, $toShareItem, $backpackFromItem) {
            $this->removeItemsFromBackpack($backpackFromItem, $count);
            $this->addItemsToBackpack($user, $toShareItem, $amountToExchange);
        });
    }

    public function getItemsForExchange(Structure $exchange, User $user): Collection
    {
        $bagItems = $this->getUserBackpackItems($user);

        return $exchange->exchangeItems()
            ->with(['fromItem', 'toItem'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($item) => $this->mapToDto($item, $bagItems));
    }

    protected function mapToDto($exchangeItem, Collection $bagItems): ExchangeItemDto
    {
        return new ExchangeItemDto(
            id: $exchangeItem->id,
            fromItem: $exchangeItem->fromItem,
            toItem: $exchangeItem->toItem,
            fromAmount: $exchangeItem->from_amount,
            toAmount: $exchangeItem->to_amount,
            availableCount: $bagItems->get((string)$exchangeItem->fromItem->id, 0)
        );
    }

    protected function getUserBackpackItems(User $user): Collection
    {
        return $this->backpackService
            ->getBaseQuery($user)
            ->addSelect('items.share_item_id')
            ->get()
            ->pluck('count', 'share_item_id');
    }

    public function removeItemsFromBackpack(Backpack $backpackFromItem, int $count): void
    {
        if ($backpackFromItem->count <= $count) {
            $backpackFromItem->delete();
            $backpackFromItem->item()->delete();
        } else {
            $backpackFromItem->decrement('count', $count);
        }
    }

    public function addItemsToBackpack(User $user, ShareItem $toShareItem, int $amountToExchange): void
    {
        $backpackToItem = $this->backpackService->getItem($user, $toShareItem);
        if ($backpackToItem instanceof Backpack) {
            $backpackToItem->increment('count', $amountToExchange);
            $backpackToItem->save();
        } else {
            $item = Item::create([
                'share_item_id' => $toShareItem->id,
            ]);

            $user->backpack()->attach($item->id, [
                'equipped' => 0,
                'count' => $amountToExchange
            ]);
        }
    }
}
