<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\RuneActionDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Services\RuneService;

class OpenRuneSlot
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

        $keySlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, (int) $data->keyId, [
            ShareItemType::RUNE_KEY->value,
        ]);

        if ($keySlot === null) {
            return new BlacksmithActionResultDTO(false, 'Рунный ключ не найден.');
        }

        $result = $this->transactionManager->run(fn () => $this->runeService->openSlot(
            $itemSlot->item,
            $keySlot,
        ));

        return new BlacksmithActionResultDTO($result['success'], $result['message'], $result['success']);
    }
}
