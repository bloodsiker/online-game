<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Structure\Blacksmith\Domain\Results\UpgradeTransferResult;

final class UpgradeTransferService
{
    public const REQUIRED_ITEM_NAME = 'Трансгрессор';

    public function successChance(ItemRarity $rarity): int
    {
        return match ($rarity) {
            ItemRarity::COMMON => 30,
            ItemRarity::UNCOMMON => 45,
            ItemRarity::RARE => 60,
            ItemRarity::EPIC => 75,
            ItemRarity::LEGENDARY => 90,
            ItemRarity::HEROIC => 100,
        };
    }

    public function transfer(
        Item $source,
        Item $target,
        Backpack $transgressorSlot,
        ?int $roll = null,
    ): UpgradeTransferResult {
        $level = (int) $source->upgrade_lvl;
        $chance = $this->successChance($transgressorSlot->item->itemInfo->rarity);
        $success = ($roll ?? random_int(1, 100)) <= $chance;

        $source->upgrade_lvl = 0;
        $source->upgrade_pity = 0;
        $source->upgrade_fail_streak = 0;
        $source->save();

        if ($success) {
            $target->upgrade_lvl = $level;
            $target->upgrade_pity = 0;
            $target->upgrade_fail_streak = 0;
            $target->save();
        }

        $this->consumeTransgressor($transgressorSlot);

        return new UpgradeTransferResult($success, $level, $chance);
    }

    private function consumeTransgressor(Backpack $slot): void
    {
        if ($slot->count > 1) {
            $slot->count--;
            $slot->save();

            return;
        }

        $item = $slot->item;
        $slot->delete();
        $item->delete();
    }
}
