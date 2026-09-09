<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\TransferUpgradeDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanTransferUpgrade;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeTransferService;

final readonly class TransferUpgrade
{
    public function __construct(
        private BlacksmithInventoryRepository $inventoryRepository,
        private TransactionManager $transactionManager,
        private CanTransferUpgrade $canTransferUpgrade,
        private UpgradeTransferService $transferService,
    ) {}

    public function execute(TransferUpgradeDTO $data): BlacksmithActionResultDTO
    {
        if ($data->sourceItemId === $data->targetItemId) {
            return new BlacksmithActionResultDTO(false, 'Выберите два разных предмета.');
        }

        return $this->transactionManager->run(function () use ($data): BlacksmithActionResultDTO {
            $slots = $this->inventoryRepository
                ->findOwnedSlotsForUpdate($data->user, [$data->sourceItemId, $data->targetItemId])
                ->keyBy('item_id');
            $sourceSlot = $slots->get($data->sourceItemId);
            $targetSlot = $slots->get($data->targetItemId);

            if ($sourceSlot === null || $targetSlot === null) {
                return new BlacksmithActionResultDTO(false, 'Один из выбранных предметов не найден в рюкзаке.');
            }

            $source = $sourceSlot->item;
            $target = $targetSlot->item;

            if ((int) $source->upgrade_lvl < 1) {
                return new BlacksmithActionResultDTO(false, 'На исходном предмете нет заточки для переноса.');
            }

            if ((int) $target->upgrade_lvl !== 0) {
                return new BlacksmithActionResultDTO(false, 'Целевой предмет уже заточен. Выберите предмет без заточки.');
            }

            if (! $this->canTransferUpgrade->check($source->itemInfo, $target->itemInfo)) {
                return new BlacksmithActionResultDTO(false, 'Заточку можно переносить только между предметами одного типа и слота.');
            }

            $transgressorSlot = $this->inventoryRepository->findOwnedSlotByShareItemIdForUpdate(
                $data->user,
                $data->transgressorShareItemId,
            );

            $transgressor = $transgressorSlot?->item?->itemInfo;
            if ($transgressor === null
                || $transgressor->name !== UpgradeTransferService::REQUIRED_ITEM_NAME
                || $transgressor->type !== ShareItemType::MISC) {
                return new BlacksmithActionResultDTO(false, 'Выбранный Трансгрессор не найден в рюкзаке.');
            }

            $result = $this->transferService->transfer($source, $target, $transgressorSlot);

            if (! $result->success) {
                return new BlacksmithActionResultDTO(
                    ok: true,
                    message: sprintf(
                        'Перенос не удался. Заточка +%d на предмете «%s» потеряна. Трансгрессор израсходован.',
                        $result->level,
                        $source->itemInfo->name,
                    ),
                    success: false,
                );
            }

            return new BlacksmithActionResultDTO(
                ok: true,
                message: sprintf(
                    'Заточка +%d перенесена с предмета «%s» на «%s». Трансгрессор израсходован.',
                    $result->level,
                    $source->itemInfo->name,
                    $target->itemInfo->name,
                ),
                success: true,
            );
        });
    }
}
