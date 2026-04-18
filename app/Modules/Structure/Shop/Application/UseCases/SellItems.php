<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\Structure\Shop\Domain\Contracts\TransactionManager;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class SellItems
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
    ) {}

    /**
     * @param  array<int, array{selected: int, count: int}>  $checkedItems  keyed by item_id
     */
    public function execute(User $user, array $checkedItems): ShopResultDTO
    {
        $filtered = array_filter(
            $checkedItems,
            fn ($p) => isset($p['selected']) && $p['selected'] == 1,
        );

        if (empty($filtered)) {
            return new ShopResultDTO(false, 'Не выбраны предметы для продажи');
        }

        $items = $this->readRepository->getSelectedSellableItems($user->id, array_map('intval', array_keys($filtered)));

        $total = 0;
        $idsToDelete = [];

        $this->transactionManager->run(function () use ($filtered, $items, $user, &$total, &$idsToDelete): void {
            foreach ($items as $sellItem) {
                $requested = (int) ($filtered[$sellItem->item_id]['count'] ?? 0);

                if ($requested <= 0) {
                    continue;
                }

                if ($requested < $sellItem->count) {
                    $total += (int) round($sellItem->item->itemInfo->price / 2) * $requested;
                    $sellItem->count -= $requested;
                    $this->inventoryRepository->saveBackpackItem($sellItem);
                } else {
                    $total += (int) round($sellItem->item->itemInfo->price / 2) * $sellItem->count;
                    $idsToDelete[] = (int) $sellItem->item_id;
                }
            }

            $user->money += $total;
            $this->inventoryRepository->saveUser($user);

            if ($idsToDelete !== []) {
                $this->inventoryRepository->deleteBackpackItems($user->id, $idsToDelete);
                $this->inventoryRepository->deleteItemsByIds($idsToDelete);
            }
        });

        return new ShopResultDTO(true, sprintf('Продано на %s монет', number_format($total, 0, ',', ' ')));
    }
}
