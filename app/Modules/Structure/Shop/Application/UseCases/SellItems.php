<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Models\Item\Item;
use App\Models\User;

class SellItems
{
    /**
     * @param  array<int, array{selected: int, count: int}>  $checkedItems  keyed by item_id
     * @return array{ok: bool, message: string, total: int}
     */
    public function execute(User $user, array $checkedItems): array
    {
        $filtered = array_filter(
            $checkedItems,
            fn ($p) => isset($p['selected']) && $p['selected'] == 1,
        );

        if (empty($filtered)) {
            return ['ok' => false, 'message' => 'Не выбраны предметы для продажи', 'total' => 0];
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

        $total       = 0;
        $idsToDelete = [];

        foreach ($items as $sellItem) {
            $requested = (int) ($filtered[$sellItem->item_id]['count'] ?? 0);

            if ($requested < $sellItem->count) {
                $total += (int) round($sellItem->item->itemInfo->price / 2) * $requested;
                $sellItem->count -= $requested;
                $sellItem->save();
            } else {
                $total += (int) round($sellItem->item->itemInfo->price / 2) * $sellItem->count;
                $idsToDelete[]  = $sellItem->item_id;
            }
        }

        $user->money += $total;
        $user->save();

        if (! empty($idsToDelete)) {
            Backpack::whereIn('item_id', $idsToDelete)->where('user_id', $user->id)->delete();
            Item::whereIn('id', $idsToDelete)->delete();
        }

        return [
            'ok'      => true,
            'message' => sprintf('Продано на %s монет', number_format($total, 0, ',', ' ')),
            'total'   => $total,
        ];
    }
}
