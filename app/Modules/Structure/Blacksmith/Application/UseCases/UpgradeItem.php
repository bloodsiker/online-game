<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradeItemDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanUpgradeItem;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeService;

class UpgradeItem
{
    public function __construct(
        private readonly BlacksmithInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly UpgradeService $upgradeService,
        private readonly CanUpgradeItem $canUpgradeItem,
    ) {}

    public function execute(UpgradeItemDTO $data): BlacksmithActionResultDTO
    {
        $itemSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, $data->itemId, [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
            ShareItemType::ARMOR->value,
            ShareItemType::BELT->value,
        ]);

        if ($itemSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Предмет не найден.');
        }

        if (! $this->canUpgradeItem->check($itemSlot->item->itemInfo)) {
            return new BlacksmithActionResultDTO(false, 'Предмет нельзя заточить.');
        }

        $baseScrollSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, $data->baseScrollId, [
            ShareItemType::SCROLL->value,
        ]);

        if ($baseScrollSlot === null || $baseScrollSlot->item->itemInfo->upgrade_scroll_type !== UpgradeScrollType::BASE) {
            return new BlacksmithActionResultDTO(false, 'Свиток заточки не найден. Для заточки необходим свиток заточки.');
        }

        $bonusScrollSlot = null;
        if ($data->bonusScrollId !== null) {
            $bonusScrollSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, $data->bonusScrollId, [
                ShareItemType::SCROLL->value,
            ]);

            if (
                $bonusScrollSlot !== null &&
                ! in_array($bonusScrollSlot->item->itemInfo->upgrade_scroll_type, [
                    UpgradeScrollType::PROTECTION,
                    UpgradeScrollType::STABILIZER,
                    UpgradeScrollType::LUCKY,
                ], true)
            ) {
                $bonusScrollSlot = null;
            }
        }

        $result = $this->transactionManager->run(fn () => $this->upgradeService->upgrade(
            $data->user,
            $itemSlot,
            $baseScrollSlot,
            $bonusScrollSlot,
        ));

        return BlacksmithActionResultDTO::fromUpgradeResult($result);
    }
}
