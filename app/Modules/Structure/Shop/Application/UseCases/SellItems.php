<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Item\Domain\Enums\ItemActionType;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Domain\Contracts\ShopInventoryRepository;
use App\Modules\Structure\Shop\Domain\Contracts\ShopReadRepository;
use App\Modules\Structure\Shop\Domain\Contracts\TransactionManager;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class SellItems
{
    public function __construct(
        private readonly ShopReadRepository $readRepository,
        private readonly ShopInventoryRepository $inventoryRepository,
        private readonly TransactionManager $transactionManager,
        private readonly ItemActionLogger $itemActionLogger,
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

        $updatedBalance = null;
        $result = $this->transactionManager->run(function () use ($filtered, $user, &$updatedBalance): ShopResultDTO {
            $lockedUser = $this->inventoryRepository->lockUser((int) $user->id);
            $items = $this->readRepository->getSelectedSellableItems(
                userId: (int) $lockedUser->id,
                itemIds: array_map('intval', array_keys($filtered)),
                forUpdate: true,
            );
            $total = 0;
            $sales = [];
            foreach ($items as $sellItem) {
                $quantity = min(max(0, (int) ($filtered[$sellItem->item_id]['count'] ?? 0)), (int) $sellItem->count);
                if ($quantity <= 0) {
                    continue;
                }
                $unitPrice = max(0, (int) round($sellItem->item->itemInfo->price / 2));
                if ($unitPrice > intdiv(WalletBalanceLimits::MAX_BALANCE - $total, $quantity)) {
                    return new ShopResultDTO(false, 'Превышен допустимый баланс кошелька.');
                }
                $money = $unitPrice * $quantity;
                $total += $money;
                $sales[] = [$sellItem, $quantity, $money];
            }
            if ($sales === []) {
                return new ShopResultDTO(false, 'Нет доступных предметов для продажи.');
            }
            if ($total > 0 && ! WalletBalanceLimits::canCredit((int) $lockedUser->money, $total)) {
                return new ShopResultDTO(false, 'Превышен допустимый баланс кошелька.');
            }
            foreach ($sales as [$sellItem, $quantity, $money]) {
                if (! $this->inventoryRepository->consumeBackpackItem($sellItem, $quantity)) {
                    throw new RuntimeException('Предметы для продажи изменились. Повторите попытку.');
                }
                $this->itemActionLogger->log(
                    user: $lockedUser,
                    shareItem: $sellItem->item->itemInfo,
                    upgradeLvl: (int) $sellItem->item->upgrade_lvl,
                    action: ItemActionType::SELL,
                    count: $quantity,
                    money: $money,
                );
            }
            $this->inventoryRepository->creditMoney($lockedUser, $total);
            $updatedBalance = (int) $lockedUser->money;

            return new ShopResultDTO(true, sprintf('Продано на %s монет', number_format($total, 0, ',', ' ')));
        });
        if ($result->ok && $updatedBalance !== null) {
            $user->money = $updatedBalance;
        }

        return $result;
    }
}
