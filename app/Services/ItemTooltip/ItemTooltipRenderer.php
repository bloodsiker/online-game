<?php

namespace App\Services\ItemTooltip;

class ItemTooltipRenderer
{
    public function render(array $items): string
    {
        return '<script>window.itemTooltip = '.
            json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).
            ';</script>';
    }
}
