<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeInventoryRepository;
use App\Modules\Structure\Exchange\Domain\Contracts\ExchangeReadRepository;
use App\Modules\Structure\Exchange\Domain\Contracts\TransactionManager;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use DomainException;

readonly class ExchangeItemService
{
    public function __construct(
        private ExchangeReadRepository $readRepository,
        private ExchangeInventoryRepository $inventoryRepository,
        private TransactionManager $transactionManager,
    ) {}

    public function performExchange(User $user, int $exchangeId, int $fromShareId, int $toShareId, int $count): void
    {
        if ($count <= 0) {
            throw new DomainException('Неверное количество для обмена.');
        }

        $fromShareItem = $this->inventoryRepository->findShareItem($fromShareId);

        if ($fromShareItem === null) {
            throw new DomainException('Предмет для обмена не найден.');
        }

        $backpackFromItem = $this->inventoryRepository->findBackpackItem($user, $fromShareId);

        if (! $backpackFromItem instanceof Backpack) {
            throw new DomainException('У вас нет необходимого предмета для обмена.');
        }

        if ($backpackFromItem->count < $count) {
            throw new DomainException('У вас недостаточно предметов для обмена.');
        }

        $toShareItem = $this->inventoryRepository->findShareItem($toShareId);

        if ($toShareItem === null) {
            throw new DomainException('Предмет результата обмена не найден.');
        }

        $exchangeItem = $this->readRepository->findExchangeItem($exchangeId, $fromShareId, $toShareId);

        if ($exchangeItem === null) {
            throw new DomainException('Указанный обмен недоступен.');
        }

        $exchangeRate = $exchangeItem->to_amount / $exchangeItem->from_amount;
        $amountToExchange = $count * $exchangeRate;

        $this->transactionManager->run(function () use ($user, $count, $amountToExchange, $toShareItem, $backpackFromItem): void {
            $this->removeItemsFromBackpack($backpackFromItem, $count);
            $this->addItemsToBackpack($user, $toShareItem->id, (int) $amountToExchange);
        });
    }

    public function removeItemsFromBackpack(Backpack $backpackFromItem, int $count): void
    {
        if ($backpackFromItem->count <= $count) {
            $this->inventoryRepository->deleteBackpackItem($backpackFromItem);
            $this->inventoryRepository->deleteItemById((int) $backpackFromItem->item_id);
        } else {
            $backpackFromItem->count -= $count;
            $this->inventoryRepository->saveBackpackItem($backpackFromItem);
        }
    }

    public function addItemsToBackpack(User $user, int $toShareItemId, int $amountToExchange): void
    {
        $backpackToItem = $this->inventoryRepository->findBackpackItem($user, $toShareItemId);

        if ($backpackToItem instanceof Backpack) {
            $backpackToItem->count += $amountToExchange;
            $this->inventoryRepository->saveBackpackItem($backpackToItem);
        } else {
            $this->inventoryRepository->createBackpackItem($user, $toShareItemId, $amountToExchange);
        }
    }
}
