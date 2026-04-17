<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Item\Item;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\BreakItemDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Services\BreakService;

class BreakItem
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly BlacksmithInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly BreakService $breakService,
    ) {}

    public function execute(BreakItemDTO $data): BlacksmithActionResultDTO
    {
        $this->readRepository->findStructureOrFail($data->blacksmithId);
        $crystal = $this->readRepository->findCrystalOrFail();
        $item = $this->inventoryRepository->findOwnedSlot($data->user, $data->itemId, ['item', 'item.itemInfo']);

        if ($item === null) {
            return new BlacksmithActionResultDTO(false, 'Не найден предмет для кристализации');
        }

        $salvageResult = $this->breakService->salvage($item->item->itemInfo);

        if (! $salvageResult->success) {
            return BlacksmithActionResultDTO::fromSalvageResult($salvageResult);
        }

        return $this->transactionManager->run(function () use ($data, $item, $crystal, $salvageResult) {
            $hasBackpack = $this->inventoryRepository->findOwnedSlotByShareItemId($data->user, $crystal->id);

            $countCrystal = $salvageResult->crystalCount;

            if ($hasBackpack !== null && $crystal->type === ShareItemType::RESOURCE) {
                $hasBackpack->count += $countCrystal;
                $hasBackpack->save();
            } else {
                $newItem = new Item;
                $newItem->share_item_id = $crystal->id;
                $newItem->save();

                $backpack = new Backpack;
                $backpack->user_id = $data->user->id;
                $backpack->item_id = $newItem->id;
                $backpack->count = $countCrystal;
                $backpack->save();
            }

            $item->delete();
            $item->item->delete();

            return BlacksmithActionResultDTO::fromSalvageResult($salvageResult);
        });
    }
}
