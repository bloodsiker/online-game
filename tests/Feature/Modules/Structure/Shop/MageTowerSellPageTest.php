<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Shop;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\DTOs\ShopSellPageDTO;
use App\Modules\Structure\Shop\Application\UseCases\GetSellPage;
use App\Modules\Structure\Shop\Presentation\Http\ShopController;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
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
        $view = app(ShopController::class)->sellItem(40);
        $html = $view->render();
        $this->assertSame('mage_tower', $view->getData()['indexRoute']);
        $this->assertStringContainsString('href="'.route('mage_tower', ['id' => 40]).'"', $html);
        $this->assertStringContainsString('href="'.route('shop.sell_item', ['id' => 40]).'"', $html);
        $this->assertStringContainsString('action="'.route('shop.sell_item.cart_add', ['id' => 40]).'"', $html);
        $this->assertStringNotContainsString('href="'.route('shop', ['id' => 40]).'"', $html);
    }

    public function test_normal_shop_still_uses_its_purchase_route(): void
    {
        $view = app(ShopController::class)->sellItem(8);
        $this->assertSame('shop', $view->getData()['indexRoute']);
    }

    public function test_unrelated_structure_has_no_sale_page(): void
    {
        $this->expectException(ModelNotFoundException::class);
        app(ShopController::class)->sellItem(9);
    }
}
