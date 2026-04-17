<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Models\Item\Item;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class SellItems
{
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

        $items = Backpack::select('backpacks.*')
            ->with('item.itemInfo')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->whereIn('item_id', array_keys($filtered))
            ->where('equipped', 0)
            ->where('share_items.is_sell', 1)
            ->get();

        $total = 0;
        $idsToDelete = [];

        foreach ($items as $sellItem) {
            $requested = (int) ($filtered[$sellItem->item_id]['count'] ?? 0);

            if ($requested < $sellItem->count) {
                $total += (int) round($sellItem->item->itemInfo->price / 2) * $requested;
                $sellItem->count -= $requested;
                $sellItem->save();
            } else {
                $total += (int) round($sellItem->item->itemInfo->price / 2) * $sellItem->count;
                $idsToDelete[] = $sellItem->item_id;
            }
        }

        $user->money += $total;
        $user->save();

        if (! empty($idsToDelete)) {
            Backpack::whereIn('item_id', $idsToDelete)->where('user_id', $user->id)->delete();
            Item::whereIn('id', $idsToDelete)->delete();
        }

        return new ShopResultDTO(true, sprintf('Продано на %s монет', number_format($total, 0, ',', ' ')));
    }
}
