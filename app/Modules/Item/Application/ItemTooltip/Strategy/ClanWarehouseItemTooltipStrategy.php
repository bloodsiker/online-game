<?php

namespace App\Modules\Item\Application\ItemTooltip\Strategy;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipPriceFormatter;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRelationLoader;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;
use App\Modules\Share\Domain\Enums\ShareItemType;

class ClanWarehouseItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        $warehouseItems = ItemTooltipRelationLoader::load($this->items, [
            'item.itemInfo.stats',
            'item.itemInfo.effects',
            'item.itemInfo.useLimit',
            'item.itemInfo.lockpickConfig',
            'item.itemInfo.requirements.skill',
            'item.treasureHunt.targetMap',
            'item.gems.gemInfo',
            'item.runes.runeInfo',
        ]);

        foreach ($warehouseItems as $warehouseItem) {
            $item = $warehouseItem->item;
            $itemInfo = $item->itemInfo;
            $upgradeLvl = $item->upgrade_lvl ?? 0;
            $title = $upgradeLvl > 0
                ? sprintf('%s <span style="color:#2255aa;font-weight:bold;">+%d</span>', $itemInfo->name, $upgradeLvl)
                : $itemInfo->name;
            if ($itemInfo->type === ShareItemType::TREASURE_MAP) {
                $title = $item->getNameWithTreasureTerritory();
            }

            $collector->add(new ItemTooltipDto(
                id: $item->id,
                title: $title,
                color: $itemInfo->rarity->color(),
                image: $itemInfo->image,
                kind: $itemInfo->getTypeName(),
                price: ItemTooltipPriceFormatter::money((int) $itemInfo->price),
                diamond: '',
                lev: ['title' => __(' Уровень '), 'value' => '1'],
                skills: [],
                desc: $item->getDescription(),
                store: false,
                nogive: ! $itemInfo->is_sell,
                noweight: ! $itemInfo->is_weight,
                nosell: ! $itemInfo->is_sell,
                stats: ItemTooltipStatsBuilder::buildForTooltip($itemInfo, $upgradeLvl),
                requirements: ItemTooltipStatsBuilder::buildRequirements($itemInfo),
                gems: ItemTooltipStatsBuilder::buildGems($item),
                runes: ItemTooltipStatsBuilder::buildRunes($item),
                remainingUses: $item->remainingUses(),
                specialInfo: array_merge(ItemTooltipStatsBuilder::buildSpecialInfo($itemInfo), $item->expiryTooltipInfo()),
            ));
        }
    }
}
