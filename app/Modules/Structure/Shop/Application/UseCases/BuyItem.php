<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\UseCases;

use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Share\ShareItem;
use App\Models\User;

class BuyItem
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function execute(User $user, int $shareItemId, int $count): array
    {
        $shareItem   = ShareItem::find($shareItemId);
        $totalCost   = $count * $shareItem->price;

        if ($user->money < $totalCost) {
            return ['ok' => false, 'message' => 'Не достаточно монет для покупки.'];
        }

        $user->money -= $totalCost;
        $user->save();

        $existing = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('items.share_item_id', $shareItem->id)
            ->first();

        if ($existing instanceof Backpack && $shareItem->type === ShareItemType::RESOURCE) {
            $existing->count += $count;
            $existing->save();
        } else {
            for ($i = 1; $i <= $count; $i++) {
                $item               = new Item;
                $item->share_item_id = $shareItem->id;
                $item->count_use    = $shareItem->count_use;
                $item->save();

                $user->backpack()->attach($item->id, ['equipped' => 0, 'count' => 1]);
            }
        }

        return ['ok' => true, 'message' => sprintf('Куплено %s %s шт', $shareItem->name, $count)];
    }
}