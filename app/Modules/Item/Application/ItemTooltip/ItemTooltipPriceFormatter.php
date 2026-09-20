<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemTooltip;

final class ItemTooltipPriceFormatter
{
    public static function money(int $price): string
    {
        if ($price === 0) {
            return '';
        }

        return sprintf(
            '<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s',
            asset('img/icon/m_game.gif'),
            number_format($price, 0, '', ' '),
        );
    }
}
