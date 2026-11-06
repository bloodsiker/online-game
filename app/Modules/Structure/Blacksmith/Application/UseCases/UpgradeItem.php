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
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeScrollGrade;
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

        $requiredType = UpgradeScrollGrade::requiredScrollType($itemSlot->item->itemInfo->type);

        if ($baseScrollSlot === null || ! ($baseScrollSlot->item->itemInfo->upgrade_scroll_type?->isBase() ?? false)) {
            return new BlacksmithActionResultDTO(false, 'Свиток заточки не найден. Для заточки необходим свиток заточки.');
        }

        if ($baseScrollSlot->item->itemInfo->upgrade_scroll_type !== $requiredType) {
            return new BlacksmithActionResultDTO(false, sprintf('Этот свиток не подходит. Нужен «%s».', $requiredType->label()));
        }

        $currentLevel = (int) $itemSlot->item->upgrade_lvl;
        if ($currentLevel < UpgradeService::MAX_LEVEL
            && ! UpgradeScrollGrade::fits($baseScrollSlot->item->itemInfo->rarity, $currentLevel)) {
            $required = UpgradeScrollGrade::requiredRarity($currentLevel);

            return new BlacksmithActionResultDTO(false, sprintf(
                'Этот свиток не подходит. Для заточки до +%d нужен свиток заточки редкости «%s».',
                $currentLevel + 1,
                $required?->label() ?? '—',
            ));
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
