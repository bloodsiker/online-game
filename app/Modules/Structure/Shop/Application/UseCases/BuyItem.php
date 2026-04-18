<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Enums\ShareItemType;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\Structure\Shop\Domain\Contracts\TransactionManager;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class BuyItem
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    public function execute(User $user, int $shareItemId, int $count): ShopResultDTO
    {
        $shareItem = $this->readRepository->findShareItem($shareItemId);

        if ($shareItem === null || $count <= 0) {
            return new ShopResultDTO(false, 'Предмет для покупки не найден.');
        }

        $totalCost = $count * $shareItem->price;

        if ($user->money < $totalCost) {
            return new ShopResultDTO(false, 'Не достаточно монет для покупки.');
        }

        $this->transactionManager->run(function () use ($user, $shareItem, $count, $totalCost): void {
            $user->money -= $totalCost;
            $this->inventoryRepository->saveUser($user);

            $existing = $this->readRepository->findResourceBackpackItem($user->id, $shareItem->id);

            if ($existing !== null && $shareItem->type === ShareItemType::RESOURCE) {
                $existing->count += $count;
                $this->inventoryRepository->saveBackpackItem($existing);

                return;
            }

            for ($i = 0; $i < $count; $i++) {
                $this->inventoryRepository->createBackpackItem(
                    $user->id,
                    $shareItem->id,
                    (int) $shareItem->count_use,
                    1,
                );
            }
        });

        return new ShopResultDTO(true, sprintf('Куплено %s %s шт', $shareItem->name, $count));
    }
}
