<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\RuneActionDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Services\RuneService;

class ImbueRune
{
    public function __construct(
        private readonly BlacksmithInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly RuneService $runeService,
    ) {}

    public function execute(RuneActionDTO $data): BlacksmithActionResultDTO
    {
        $itemSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, $data->itemId, [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
        ]);

        if ($itemSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Предмет не найден.');
        }

        $runeSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, (int) $data->runeId, [
            ShareItemType::RUNE->value,
        ]);

        if ($runeSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Руна не найдена.');
        }

        $result = $this->transactionManager->run(fn () => $this->runeService->imbue(
            $data->user,
            $itemSlot->item,
            $runeSlot,
            (int) $data->slotIndex,
            $data->riskMode,
        ));

        return new BlacksmithActionResultDTO($result['success'], $result['message'], $result['success'], $result['destroyed'] ?? false);
    }
}
