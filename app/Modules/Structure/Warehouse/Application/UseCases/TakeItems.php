<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Structure;
use App\Models\User;
use App\Models\Warehouse;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Warehouse\Application\DTOs\WarehouseResultDTO;

class TakeItems
{
    /**
     * @param  array<int, array{selected: int, count: int}>  $checkedItems  keyed by item_id
     */
    public function execute(User $user, Structure $warehouse, array $checkedItems): WarehouseResultDTO
    {
        $takeItems = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

        if (empty($takeItems)) {
            return new WarehouseResultDTO(false, 'Не выбраны предметы которые хотите забрать.');
        }

        $items = Warehouse::with(['item', 'item.itemInfo'])
            ->where('structure_id', $warehouse->id)
            ->where('user_id', $user->id)
            ->whereIn('item_id', array_keys($takeItems))
            ->get();

        foreach ($items as $wItem) {
            $wantCount   = (int) ($takeItems[$wItem->item_id]['count'] ?? $wItem->count);
            $actualCount = min($wantCount, $wItem->count);

            if ($wItem->count <= $actualCount) {
                $wItem->delete();
            } else {
                $wItem->count -= $actualCount;
                $wItem->save();
            }

            $existing = null;
            if ($wItem->item->itemInfo->type === ShareItemType::RESOURCE || $wItem->item->itemInfo->type === ShareItemType::POTION) {
                $existing = Backpack::select('backpacks.*')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('items.share_item_id', $wItem->item->share_item_id)
                    ->where('backpacks.user_id', $user->id)
                    ->first();
            }

            if ($existing) {
                $existing->count += $actualCount;
                $existing->save();
            } else {
                Backpack::create([
                    'user_id' => $user->id,
                    'item_id' => $wItem->item_id,
                    'count'   => $actualCount,
                ]);
            }
        }

        return new WarehouseResultDTO(true, '');
    }
}