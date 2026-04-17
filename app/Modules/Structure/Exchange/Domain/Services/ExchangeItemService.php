<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Domain\Services;

use App\Models\Item\Item;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;

readonly class ExchangeItemService
{
    public function __construct(
        private BackpackService $backpackService,
    ) {}

    public function performExchange(User $user, Structure $exchange, int $fromShareId, int $toShareId, int $count): void
    {
        if ($count <= 0) {
            throw new DomainException('Неверное количество для обмена.');
        }

        $fromShareItem = ShareItem::find($fromShareId);

        if (! $fromShareItem instanceof ShareItem) {
            throw new DomainException('Предмет для обмена не найден.');
        }

        $backpackFromItem = $this->backpackService->getItem($user, $fromShareItem);

        if (! $backpackFromItem instanceof Backpack) {
            throw new DomainException('У вас нет необходимого предмета для обмена.');
        }

        if ($backpackFromItem->count < $count) {
            throw new DomainException('У вас недостаточно предметов для обмена.');
        }

        $toShareItem = ShareItem::find($toShareId);

        if (! $toShareItem instanceof ShareItem) {
            throw new DomainException('Предмет результата обмена не найден.');
        }

        $exchangeItem = $exchange->exchangeItems()
            ->where('from_share_item_id', $fromShareItem->id)
            ->where('to_share_item_id', $toShareItem->id)
            ->first();

        if ($exchangeItem === null) {
            throw new DomainException('Указанный обмен недоступен.');
        }

        $exchangeRate = $exchangeItem->to_amount / $exchangeItem->from_amount;
        $amountToExchange = $count * $exchangeRate;

        \DB::transaction(function () use ($user, $count, $amountToExchange, $toShareItem, $backpackFromItem) {
            $this->removeItemsFromBackpack($backpackFromItem, $count);
            $this->addItemsToBackpack($user, $toShareItem, $amountToExchange);
        });
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
            $item = Item::create(['share_item_id' => $toShareItem->id]);

            $user->backpack()->attach($item->id, ['equipped' => 0, 'count' => $amountToExchange]);
        }
    }
}
