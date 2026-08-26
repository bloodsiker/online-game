<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemTooltip\Strategy;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRelationLoader;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;

final class ItemModelTooltipStrategy implements ItemTooltipStrategyInterface
{
    /**
     * @param  iterable<int, Item>  $items
     */
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        $items = ItemTooltipRelationLoader::load($this->items, [
            'itemInfo.stats',
            'itemInfo.effects',
            'itemInfo.requirements.skill',
            'gems.gemInfo',
            'runes.runeInfo',
        ]);

        foreach ($items as $item) {
            $itemInfo = $item->itemInfo;
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
                nogive: ! $itemInfo->is_give,
                noweight: ! $itemInfo->is_weight,
                nosell: ! $itemInfo->is_sell,
                stats: ItemTooltipStatsBuilder::build($itemInfo, $upgradeLvl),
                requirements: ItemTooltipStatsBuilder::buildRequirements($itemInfo),
                gems: ItemTooltipStatsBuilder::buildGems($item),
                runes: ItemTooltipStatsBuilder::buildRunes($item),
            ));
        }
    }
}
