<?php

namespace App\Services\ItemTooltip\Strategy;

use App\Services\ItemTooltip\ItemTooltipCollector;

interface ItemTooltipStrategyInterface
{
    public function collect(ItemTooltipCollector $collector): void;
}