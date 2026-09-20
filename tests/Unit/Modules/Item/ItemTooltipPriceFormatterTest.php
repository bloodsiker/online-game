<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Item;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipPriceFormatter;
use Tests\TestCase;

final class ItemTooltipPriceFormatterTest extends TestCase
{
    public function test_zero_price_is_hidden(): void
    {
        $this->assertSame('', ItemTooltipPriceFormatter::money(0));
    }

    public function test_positive_price_is_rendered(): void
    {
        $price = ItemTooltipPriceFormatter::money(1250);

        $this->assertStringContainsString('img/icon/m_game.gif', $price);
        $this->assertStringEndsWith(' 1 250', $price);
    }
}
