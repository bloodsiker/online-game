<?php

declare(strict_types=1);

namespace App\Services\ItemTooltip\Strategy;

use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\ItemTooltipDto;
use App\Services\ItemTooltip\ItemTooltipStatsBuilder;

class ShareItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->items as $shareItem) {
            $shareItem->loadMissing('requirements.skill');
            $collector->add(new ItemTooltipDto(
                id: $shareItem->id,
                title: $shareItem->name,
                color: '#000000',
                image: $shareItem->image,
                kind: $shareItem->getTypeName(),
                price: $shareItem->price
                    ? sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $shareItem->price)
                    : '',
                diamond: '',
                lev: ['title' => ' Уровень ', 'value' => '1'],
                skills: [],
                desc: $shareItem->description ?? '',
                store: false,
                nogive: !$shareItem->is_sell,
                noweight: !$shareItem->is_weight,
                nosell: !$shareItem->is_sell,
                stats: ItemTooltipStatsBuilder::build($shareItem),
                requirements: ItemTooltipStatsBuilder::buildRequirements($shareItem),
            ));
        }
    }
}