<?php

namespace App\Modules\Item\Application\ItemTooltip;

class ItemTooltipRenderer
{
    public function render(array $items): string
    {
        return view('interface::partials.game-translations')->render().'<script>window.itemTooltip = '.
            json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).
            ';</script>';
    }
}
