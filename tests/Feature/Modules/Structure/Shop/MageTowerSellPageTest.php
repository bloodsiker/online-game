<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Shop;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\DTOs\ShopResultDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellPageDTO;
use App\Modules\Structure\Shop\Application\UseCases\GetSellPage;
use App\Modules\Structure\Shop\Application\UseCases\SellItems;
use App\Modules\Structure\Shop\Presentation\Http\ShopController;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class MageTowerSellPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
        });
        DB::table('structures')->insert([
            ['id' => 40, 'type' => Structure::TYPE_MAGE_TOWER],
            ['id' => 8, 'type' => Structure::TYPE_SHOP],
            ['id' => 9, 'type' => Structure::TYPE_BLACKSMITH],
        ]);
        Auth::setUser((new User)->forceFill(['id' => 1]));
        $page = \Mockery::mock(GetSellPage::class);
        $page->shouldReceive('execute')->andReturnUsing(fn ($user, int $id): ShopSellPageDTO => new ShopSellPageDTO($id, 100, 0, [], ''));
        $this->app->instance(GetSellPage::class, $page);
    }

    public function test_tower_sale_page_opens_and_purchase_tab_returns_to_mage_tower(): void
    {
        $view = app(ShopController::class)->sellItem(Request::create('/shop/40/sell-item'), 40);
        $html = $view->render();
        $this->assertSame('mage_tower', $view->getData()['indexRoute']);
        $this->assertStringContainsString('href="'.route('mage_tower', ['id' => 40]).'"', $html);
        $this->assertStringContainsString('action="'.route('shop.sell_item', ['id' => 40]).'"', $html);
        $this->assertStringNotContainsString('href="'.route('shop', ['id' => 40]).'"', $html);
    }

    public function test_tower_sale_post_uses_existing_sell_items_flow(): void
    {
        $selected = [123 => ['selected' => 1, 'count' => 2]];
        $sale = \Mockery::mock(SellItems::class);
        $sale->shouldReceive('execute')->once()->with(Auth::user(), $selected)->andReturn(new ShopResultDTO(true, 'Продано'));
        $this->app->instance(SellItems::class, $sale);
        $view = app(ShopController::class)->sellItem(Request::create('/shop/40/sell-item', 'POST', ['item' => $selected]), 40);
        $this->assertSame('mage_tower', $view->getData()['indexRoute']);
        $this->assertSame('Продано', session('message'));
    }

    public function test_normal_shop_still_uses_its_purchase_route(): void
    {
        $view = app(ShopController::class)->sellItem(Request::create('/shop/8/sell-item'), 8);
        $this->assertSame('shop', $view->getData()['indexRoute']);
    }

    public function test_unrelated_structure_cannot_accept_sale_posts(): void
    {
        $sale = \Mockery::mock(SellItems::class);
        $sale->shouldNotReceive('execute');
        $this->app->instance(SellItems::class, $sale);
        $this->expectException(ModelNotFoundException::class);
        app(ShopController::class)->sellItem(Request::create('/shop/9/sell-item', 'POST'), 9);
    }
}
