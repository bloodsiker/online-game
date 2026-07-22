<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Services\ItemTooltip;

use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\ItemTooltipDto;
use App\Services\ItemTooltip\ItemTooltipStatsBuilder;
use App\Services\ItemTooltip\Strategy\ItemTooltipStrategyInterface;

class BackpackItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->items as $backpack) {
            $item = $backpack->item;
            $itemInfo = $item->itemInfo;
            $itemInfo->loadMissing('requirements.skill');
            $item->loadMissing(['gems.gemInfo', 'runes.runeInfo']);
            $upgradeLvl = $item->upgrade_lvl ?? 0;
            $title = $upgradeLvl > 0
                ? sprintf('%s <span style="color:#2255aa;font-weight:bold;">+%d</span>', $itemInfo->name, $upgradeLvl)
                : $itemInfo->name;

            $collector->add(new ItemTooltipDto(
                id: $item->id,
                title: $title,
                color: $itemInfo->rarity->color(),
                image: $itemInfo->image,
                kind: $itemInfo->getTypeName(),
                price: sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $itemInfo->price),
                diamond: '',
                lev: ['title' => ' Уровень ', 'value' => '1'],
                skills: [],
                desc: $itemInfo->description ?? '',
                store: false,
                nogive: ! $itemInfo->is_sell,
                noweight: ! $itemInfo->is_weight,
                nosell: ! $itemInfo->is_sell,
                stats: ItemTooltipStatsBuilder::build($itemInfo),
                requirements: ItemTooltipStatsBuilder::buildRequirements($itemInfo),
                gems: ItemTooltipStatsBuilder::buildGems($item),
                runes: ItemTooltipStatsBuilder::buildRunes($item),
            ));
        }
    }
}
