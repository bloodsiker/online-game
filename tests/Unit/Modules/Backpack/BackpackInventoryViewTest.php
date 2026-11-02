<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Backpack;

use App\Modules\Backpack\Domain\DTO\BackpackDTO;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class BackpackInventoryViewTest extends TestCase
{
    public function test_large_categories_keep_separate_lists_and_all_item_action_attributes(): void
    {
        $weapons = new Collection;
        for ($id = 1; $id <= 120; $id++) {
            $weapons->push($this->backpackItem($id, 'weapon'));
        }
        $shield = $this->backpackItem(121, 'shield');
        $food = $this->backpackItem(122, 'eat');
        $cargo = $this->backpackItem(123, 'trade_goods', 5);
        $data = new BackpackDTO(
            backpack: new Collection,
            items: new Collection([
                'weapon' => $weapons,
                'shield' => new Collection([$shield]),
                'eat' => new Collection([$food]),
                'trade_goods' => new Collection([$cargo]),
            ]),
            countItems: 123,
            group: 'main',
        );

        $html = view('backpack::partials.inventory', compact('data'))->render();
        $xpath = $this->xpath($html);

        $this->assertSame(3, $xpath->query('//section')->length);
        $this->assertSame(121, $xpath->query('//section[@id="bag_section_equipment"]/ul/li')->length);
        $this->assertSame(1, $xpath->query('//section[@id="bag_section_food"]/ul/li')->length);
        $this->assertSame(123, $xpath->query('//li[@data-backpack-id]')->length);
        $this->assertSame(0, $xpath->query('//*[@id = preceding::*/@id]')->length);
        $this->assertSame('equipment,food,trade_goods', implode(',', array_map(
            fn ($section) => str_replace('bag_section_', '', $section->getAttribute('id')),
            iterator_to_array($xpath->query('//section')),
        )));
        $this->assertSame(1, $xpath->query('//section[@id="bag_section_trade_goods"]//td[@data-count="5" and @data-type="trade_goods" and @onclick="showCtxMenu(this, event)"]')->length);
        $this->assertSame(1, $xpath->query('//section[@id="bag_section_trade_goods"]//div[@class="bpdig" and text()="5"]')->length);
        $this->assertStringNotContainsString('float:', $html);
    }

    public function test_other_tabs_keep_item_tooltips_and_equipment_actions_without_category_headings(): void
    {
        $artifact = $this->backpackItem(124, 'artifact');
        $data = new BackpackDTO(
            backpack: new Collection([$artifact]),
            items: new Collection,
            countItems: 1,
            group: 'artifact',
        );

        $xpath = $this->xpath(view('backpack::partials.inventory', compact('data'))->render());

        $this->assertSame(0, $xpath->query('//section')->length);
        $this->assertSame(1, $xpath->query('//ul/li[@data-backpack-id="124"]//td[@data-wearable="1" and @onmouseover="showItemInfo(this,event,2)" and @onmouseout="showItemInfo(this,event,0)"]')->length);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $document->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }

    private function backpackItem(int $id, string $type, int $count = 1): Backpack
    {
        $shareItem = new ShareItem;
        $shareItem->id = $id;
        $shareItem->type = $type;
        $shareItem->name = 'Item '.$id;
        $shareItem->count_use = 0;

        $item = new Item;
        $item->id = $id;
        $item->share_item_id = $id;
        $item->setRelation('itemInfo', $shareItem);

        $backpack = new Backpack;
        $backpack->id = $id;
        $backpack->count = $count;
        $backpack->setRelation('item', $item);

        return $backpack;
    }
}
