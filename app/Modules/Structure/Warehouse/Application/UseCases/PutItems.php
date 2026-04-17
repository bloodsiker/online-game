<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Structure;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehouseResultDTO;

class PutItems
{
    /**
     * @param  array<int, array{selected: int, count: int}>  $checkedItems  keyed by item_id
     */
    public function execute(User $user, Structure $warehouse, array $checkedItems): WarehouseResultDTO
    {
        $putItems = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

        if (empty($putItems)) {
            return new WarehouseResultDTO(false, 'Не выбраны предметы для хранения.');
        }

        $countInWarehouse = Warehouse::where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->count();

        $items = Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->whereIn('backpacks.item_id', array_keys($putItems))
            ->where('equipped', 0)
            ->get();

        $toInsert        = [];
        $toStack         = [];
        $toSubtract      = [];
        $toDeleteItemIds = [];
        $isLimit         = false;
        $totalCost       = 0;
        $newSlots        = 0;

        foreach ($items as $item) {
            if ($countInWarehouse + $newSlots >= $user->warehouse_count) {
                $isLimit = true;
                break;
            }

            $wantCount   = (int) ($putItems[$item->item_id]['count'] ?? $item->count);
            $actualCount = min($wantCount, $item->count);
            $totalCost  += 10;

            $stackTarget = null;
            if ($item->item->itemInfo->type === ShareItemType::RESOURCE || $item->item->itemInfo->type === ShareItemType::POTION) {
                $stackTarget = Warehouse::select('warehouses.*')
                    ->join('items', 'warehouses.item_id', '=', 'items.id')
                    ->where('items.share_item_id', $item->item->share_item_id)
                    ->where('warehouses.user_id', $user->id)
                    ->where('warehouses.structure_id', $warehouse->id)
                    ->first();
            }

            if ($stackTarget) {
                $toStack[] = ['warehouse' => $stackTarget, 'add' => $actualCount];
            } else {
                $toInsert[] = [
                    'user_id'      => $user->id,
                    'structure_id' => $warehouse->id,
                    'item_id'      => $item->item_id,
                    'count'        => $actualCount,
                ];
                $newSlots++;
            }

            if ($item->count <= $actualCount) {
                $toDeleteItemIds[] = $item->item_id;
            } else {
                $toSubtract[] = ['item' => $item, 'count' => $actualCount];
            }
        }

        if ($user->money < $totalCost) {
            return new WarehouseResultDTO(false, sprintf(
                'У вас не достаточно %s монет для хранения вещей',
                number_format($totalCost, 0, ',', ' '),
            ));
        }

        foreach ($toStack as $stack) {
            $stack['warehouse']->count += $stack['add'];
            $stack['warehouse']->save();
        }

        if ($toInsert) {
            Warehouse::insert($toInsert);
        }

        foreach ($toSubtract as $sub) {
            $sub['item']->count -= $sub['count'];
            $sub['item']->save();
        }

        if ($toDeleteItemIds) {
            Backpack::whereIn('item_id', $toDeleteItemIds)->where('user_id', $user->id)->delete();
        }

        $user->money -= $totalCost;
        $user->save();

        if ($isLimit) {
            return new WarehouseResultDTO(false, 'У вас не достаточно свободных мест в хранилище.');
        }

        return new WarehouseResultDTO(true, '');
    }
}