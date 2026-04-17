<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Structure;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehouseResultDTO;
use App\Modules\Structure\Warehouse\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Warehouse\Domain\Contracts\WarehouseInventoryRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class TakeItems
{
    public function __construct(
        private readonly WarehouseInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    /**
     * @param  array<int, array{selected: int, count: int}>  $checkedItems  keyed by item_id
     */
    public function execute(User $user, Structure $warehouse, array $checkedItems): WarehouseResultDTO
    {
        $takeItems = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

        if (empty($takeItems)) {
            return new WarehouseResultDTO(false, 'Не выбраны предметы которые хотите забрать.');
        }

        $items = $this->inventoryRepository->getWarehouseItemsForTransfer($user->id, $warehouse->id, array_map('intval', array_keys($takeItems)));

        $this->transactionManager->run(function () use ($items, $takeItems, $user): void {
            foreach ($items as $wItem) {
                $wantCount = (int) ($takeItems[$wItem->item_id]['count'] ?? $wItem->count);
                $actualCount = min($wantCount, $wItem->count);

                if ($wItem->count <= $actualCount) {
                    $this->inventoryRepository->deleteWarehouseItem($wItem);
                } else {
                    $wItem->count -= $actualCount;
                    $this->inventoryRepository->saveWarehouseItem($wItem);
                }

                $existing = null;
                if ($wItem->item->itemInfo->type === ShareItemType::RESOURCE || $wItem->item->itemInfo->type === ShareItemType::POTION) {
                    $existing = $this->inventoryRepository->findBackpackStack($user->id, $wItem->item->share_item_id);
                }

                if ($existing) {
                    $existing->count += $actualCount;
                    $this->inventoryRepository->saveBackpackItem($existing);
                } else {
                    $this->inventoryRepository->createBackpackItem($user->id, $wItem->item_id, $actualCount);
                }
            }
        });

        return new WarehouseResultDTO(true, '');
    }
}
