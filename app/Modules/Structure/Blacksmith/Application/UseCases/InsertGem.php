<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemActionDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Services\GemService;

class InsertGem
{
    public function __construct(
        private readonly BlacksmithInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly GemService $gemService,
    ) {}

    public function execute(GemActionDTO $data): BlacksmithActionResultDTO
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

        $gemSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, (int) $data->gemId, [
            ShareItemType::GEM->value,
        ]);

        if ($gemSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Камень не найден.');
        }

        $result = $this->transactionManager->run(fn () => $this->gemService->insertGem(
            $data->user,
            $itemSlot,
            $gemSlot,
            (int) $data->socketIndex,
        ));

        return new BlacksmithActionResultDTO($result['success'], $result['message'], $result['success']);
    }
}
