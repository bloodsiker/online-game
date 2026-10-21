<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use Tests\TestCase;

final class AuctionItemTooltipViewTest extends TestCase
{
    public function test_new_lot_cells_show_actual_stack_quantity_and_select_the_item(): void
    {
        $item = (object) ['id' => 123, 'itemInfo' => (object) ['name' => 'Агат', 'image' => 'img/icon/d.gif']];
        $html = view('auction::new_lot', [
            'auction' => (object) ['id' => 9],
            'commissionShop' => (object) ['id' => 9],
            'exchange' => (object) ['id' => 13],
            'user' => (object) ['money' => 100, 'diamond' => 0],
            'selectedItem' => null,
            'itemsToSell' => collect([(object) ['item' => $item, 'count' => 201], (object) ['item' => $item, 'count' => 1]]),
            'filter' => ['name' => '', 'type' => ''],
            'itemTooltipScript' => '<script>window.itemTooltip={};</script>',
        ])->render();

        $this->assertStringContainsString('class="backpack_list auction-lot-items"', $html);
        $this->assertStringContainsString('<div class="bpdig">201</div>', $html);
        $this->assertSame(1, substr_count($html, '<div class="bpdig">'));
        $this->assertStringContainsString('iid=123', $html);
        $this->assertSame(2, substr_count($html, 'onmouseover="showItemInfo(this,event,2)"'));
    }

    public function test_auction_and_own_lots_have_tooltips_on_images_and_item_names(): void
    {
        $itemInfo = new class
        {
            public int $id = 123;

            public string $name = 'Тестовый предмет';

            public string $image = 'img/icon/d.gif';

            public function getTypeName(): string
            {
                return 'Свиток';
            }
        };
        foreach (['auction::list', 'auction::list_my_lot', 'auction::exchange', 'auction::my_orders'] as $viewName) {
            $html = view($viewName, [
                'auction' => (object) ['id' => 9],
                'commissionShop' => (object) ['id' => 9],
                'exchange' => (object) ['id' => 12],
                'user' => (object) ['money' => 100, 'diamond' => 0],
                'auctionSlots' => collect([(object) [
                    'id' => 2, 'item' => (object) ['id' => 123, 'itemInfo' => $itemInfo],
                    'count' => 2, 'price' => 100, 'is_anonymous' => true,
                ]]),
                'orders' => collect([(object) ['id' => 3, 'shareItem' => $itemInfo, 'count' => 2, 'price' => 100, 'is_anonymous' => true]]),
                'filter' => [],
                'itemTooltipScript' => '<script>window.itemTooltip = {"123":{}};</script>',
            ])->render();

            $this->assertSame(2, substr_count($html, 'onmouseover="showItemInfo(this,event,2)"'));
            $this->assertStringContainsString('window.itemTooltip = {"123":{}}', $html);
            $this->assertStringContainsString('js/item_tooltip.js', $html);
            $this->assertStringNotContainsString('showArtifactInfo(22195638', $html);
            $this->assertStringNotContainsString('artifactAlt(this,event', $html);
        }
    }
}
