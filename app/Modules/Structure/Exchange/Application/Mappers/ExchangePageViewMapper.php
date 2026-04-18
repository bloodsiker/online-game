<?php

declare(strict_types=1);

namespace App\Modules\Structure\Exchange\Application\Mappers;

use App\Modules\Structure\Exchange\Application\DTOs\ExchangePageDTO;
use App\Modules\Structure\Exchange\Application\DTOs\ExchangeViewItemDTO;
use App\Modules\Structure\Exchange\Infrastructure\Persistence\Models\Exchange;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class ExchangePageViewMapper
{
    /**
     * @param  Collection<int, Exchange>  $exchangeItems
     * @param  array<int, int>  $availableCounts
     */
    public function map(User $user, int $exchangeId, Collection $exchangeItems, array $availableCounts): ExchangePageDTO
    {
        return new ExchangePageDTO(
            exchangeId: $exchangeId,
            money: (int) $user->money,
            diamonds: (int) $user->diamond,
            items: $exchangeItems->map(
                static fn (Exchange $item): ExchangeViewItemDTO => new ExchangeViewItemDTO(
                    fromItemId: (int) $item->fromItem->id,
                    fromItemName: (string) $item->fromItem->name,
                    fromItemImage: (string) $item->fromItem->image,
                    toItemId: (int) $item->toItem->id,
                    toItemName: (string) $item->toItem->name,
                    toItemImage: (string) $item->toItem->image,
                    fromAmount: (int) $item->from_amount,
                    toAmount: (int) $item->to_amount,
                    availableCount: $availableCounts[(int) $item->from_share_item_id] ?? 0,
                )
            )->all(),
        );
    }
}
