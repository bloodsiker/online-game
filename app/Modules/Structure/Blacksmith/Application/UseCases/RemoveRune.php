<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\RuneActionDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Services\RuneService;

class RemoveRune
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
        ], ['item']);

        if ($itemSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Предмет не найден.');
        }

        $result = $this->transactionManager->run(fn () => $this->runeService->removeRune(
            $itemSlot->item,
            (int) $data->slotIndex,
        ));

        return new BlacksmithActionResultDTO($result['success'], $result['message'], $result['success']);
    }
}
