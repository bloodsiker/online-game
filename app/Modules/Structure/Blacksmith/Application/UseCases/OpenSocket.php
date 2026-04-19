<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemActionDTO;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Services\GemService;

class OpenSocket
{
    public function __construct(
        private readonly BlacksmithInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly GemService $gemService,
    ) {}

    public function execute(GemActionDTO $data): BlacksmithActionResultDTO
    {
        $itemSlot = $this->inventoryRepository->findOwnedSlot($data->user, $data->itemId, ['item']);

        if ($itemSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Предмет не найден.');
        }

        $kitSlot = $this->inventoryRepository->findOwnedSlotByTypes($data->user, (int) $data->kitId, [
            ShareItemType::SOCKET_KIT->value,
        ]);

        if ($kitSlot === null) {
            return new BlacksmithActionResultDTO(false, 'Набор для открытия сокета не найден.');
        }

        $result = $this->transactionManager->run(fn () => $this->gemService->openSocket(
            $data->user,
            $itemSlot->item,
            $kitSlot,
        ));

        return new BlacksmithActionResultDTO($result['success'], $result['message'], $result['success']);
    }
}
