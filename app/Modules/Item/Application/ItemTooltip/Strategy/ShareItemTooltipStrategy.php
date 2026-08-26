<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemTooltip\Strategy;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRelationLoader;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;

class ShareItemTooltipStrategy implements ItemTooltipStrategyInterface
{
    public function __construct(private readonly iterable $items) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        $shareItems = ItemTooltipRelationLoader::load($this->items, [
            'stats',
            'effects',
            'requirements.skill',
        ]);

        foreach ($shareItems as $shareItem) {
            $collector->add(new ItemTooltipDto(
                id: $shareItem->id,
                title: $shareItem->name,
                color: $shareItem->rarity->color(),
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
                nogive: ! $shareItem->is_give,
                noweight: ! $shareItem->is_weight,
                nosell: ! $shareItem->is_sell,
                stats: ItemTooltipStatsBuilder::build($shareItem),
                requirements: ItemTooltipStatsBuilder::buildRequirements($shareItem),
            ));
        }
    }
}
