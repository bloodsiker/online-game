<?php

namespace App\Modules\Item\Application\ItemTooltip\Strategy;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;

interface ItemTooltipStrategyInterface
{
    public function collect(ItemTooltipCollector $collector): void;
}
