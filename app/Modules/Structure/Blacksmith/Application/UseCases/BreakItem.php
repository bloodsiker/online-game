<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Item\Item;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\BreakItemDTO;
use Illuminate\Support\Facades\DB;

class BreakItem
{
    public function execute(BreakItemDTO $data): BlacksmithActionResultDTO
    {
        Structure::findOrFail($data->blacksmithId);
        $crystal = ShareItem::findOrFail(23);

        $item = Backpack::with(['item', 'item.itemInfo'])
            ->where('backpacks.user_id', $data->user->id)
            ->where(['item_id' => $data->itemId])
            ->first();

        if (! $item instanceof Backpack) {
            return new BlacksmithActionResultDTO(false, 'Не найден предмет для кристализации');
        }

        return DB::transaction(function () use ($data, $item, $crystal) {
            $hasBackpack = Backpack::select('backpacks.*')
                ->where(['items.share_item_id' => $crystal->id])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->where('backpacks.user_id', $data->user->id)
                ->first();

            $countCrystal = $item->item->itemInfo->break_crystal;

            if ($hasBackpack instanceof Backpack && $crystal->type === ShareItemType::RESOURCE) {
                $hasBackpack->count += $countCrystal;
                $hasBackpack->save();
            } else {
                $newItem = new Item;
                $newItem->share_item_id = $crystal->id;
                $newItem->save();

                $backpack = new Backpack;
                $backpack->user_id = $data->user->id;
                $backpack->item_id = $newItem->id;
                $backpack->count = $countCrystal;
                $backpack->save();
            }

            $item->delete();
            $item->item->delete();

            return new BlacksmithActionResultDTO(
                ok: true,
                message: sprintf('Вы получили кристаллов в количестве %s шт', $countCrystal),
                success: true,
            );
        });
    }
}
