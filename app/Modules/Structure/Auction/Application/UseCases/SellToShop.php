<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Models\Item\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SellToShop
{
    /**
     * @param  array<int, array{selected: int, count: int}>  $checkedItems  keyed by item_id
     * @return array{ok: bool, message: string, total: int}
     */
    public function execute(User $user, array $checkedItems): array
    {
        $filtered = array_filter(
            $checkedItems,
            fn ($product) => isset($product['selected']) && $product['selected'] == 1,
        );

        if (empty($filtered)) {
            return ['ok' => false, 'message' => 'Не выбраны предметы для продажи', 'total' => 0];
        }

        $total = DB::transaction(function () use ($user, $filtered): int {
            $items = Backpack::select('backpacks.*')
                ->with('item.itemInfo')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $user->id)
                ->whereIn('item_id', array_keys($filtered))
                ->where('equipped', 0)
                ->where('share_items.is_sell', 1)
                ->lockForUpdate()
                ->get();

            $sum         = 0;
            $idsToDelete = [];

            foreach ($items as $sellItem) {
                $requested = (int) ($filtered[$sellItem->item_id]['count'] ?? 0);
                $qty       = min($requested, $sellItem->count);
                $sum      += (int) round($sellItem->item->itemInfo->price / 2) * $qty;

                if ($qty >= $sellItem->count) {
                    $idsToDelete[] = $sellItem->item_id;
                } else {
                    $sellItem->decrement('count', $qty);
                }
            }

            if (! empty($idsToDelete)) {
                Backpack::whereIn('item_id', $idsToDelete)->where('user_id', $user->id)->delete();
                Item::whereIn('id', $idsToDelete)->delete();
            }

            $user->increment('money', $sum);

            return $sum;
        });

        return [
            'ok'      => true,
            'message' => sprintf('Продано на %s монет', number_format($total, 0, ',', ' ')),
            'total'   => $total,
        ];
    }
}