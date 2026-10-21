<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Structure\Auction\Presentation\Http\AuctionController;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

final class AuctionNewOrderPageTest extends TestCase
{
    public function test_new_order_paginates_twenty_items_and_keeps_selected_item_tooltip(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('locations', fn (Blueprint $table) => $table->id());
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->boolean('is_auction_sellable');
            $table->boolean('is_sell')->default(true);
            $table->integer('price')->default(10);
        });
        DB::table('structures')->insert(['id' => 13, 'type' => 'auction_exchange', 'location_id' => 5]);
        for ($id = 1; $id <= 46; $id++) {
            DB::table('share_items')->insert([
                'id' => $id, 'name' => json_encode(['ru' => sprintf('Предмет %02d', $id)]),
                'type' => 'resource', 'is_auction_sellable' => $id <= 45,
            ]);
        }

        $collector = Mockery::mock(ItemTooltipCollector::class);
        $collector->shouldReceive('collectFrom')->once()->withArgs(function ($strategy): bool {
            $this->assertInstanceOf(ShareItemTooltipStrategy::class, $strategy);
            $items = (new \ReflectionProperty($strategy, 'items'))->getValue($strategy);
            $this->assertCount(21, $items);
            $this->assertTrue($items->contains('id', 1));
            $this->assertFalse($items->contains('id', 46));

            return true;
        })->andReturnSelf();
        $collector->shouldReceive('renderScript')->once()->andReturn('<script>window.itemTooltip={};</script>');
        $this->app->instance(ItemTooltipCollector::class, $collector);
        $this->actingAs((new User)->forceFill(['id' => 1, 'money' => 100, 'diamond' => 0])->setRelation('player', null));
        $request = Request::create('/auction/13/new-order', 'GET', ['page' => 2, 'siid' => 1, 'filter' => ['name' => 'Предмет', 'type' => 'resource']]);
        $this->app->instance('request', $request);
        $view = app(AuctionController::class)->newOrder($request, 13);
        $data = $view->getData();
        $this->assertSame(20, $data['shareItems']->perPage());
        $this->assertSame(45, $data['shareItems']->total());
        $this->assertSame(2, $data['shareItems']->currentPage());
        $this->assertCount(20, $data['shareItems']);
        $html = $view->render();
        $this->assertSame(20, substr_count($html, 'class="select-item"'));
        $this->assertSame(22, substr_count($html, 'onmouseover="showItemInfo(this,event,2)"'));
        $this->assertStringContainsString('class="backpack_list auction-order-items"', $html);
        $this->assertStringNotContainsString('<span class="bpdig">', $html);
        $this->assertStringContainsString('main/images/user-reward-frame.png', $html);
        $this->assertStringContainsString('siid=21', $html);
        $this->assertStringContainsString('auction-item-pagination', $html);
        $this->assertStringContainsString('page=3', $html);
        $this->assertStringContainsString('img/bg/p-left-red.gif', $html);
        $this->assertStringContainsString('img/bg/p-right-red.gif', $html);
        $this->assertStringContainsString('class="pg-act_lnk"', $html);
        $this->assertStringContainsString('name="filter[name]" value="Предмет"', $html);
        $this->assertStringContainsString('name="filter[type]"', $html);
        $this->assertStringContainsString('filter%5Btype%5D=resource', $html);
        $this->assertStringContainsString('name="siid" value="1"', $html);
    }
}
