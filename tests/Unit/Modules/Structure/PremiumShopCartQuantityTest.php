<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Structure\PremiumShop\Application\UseCases\GetShopItems;
use App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart;
use App\Modules\Structure\PremiumShop\Presentation\Http\PremiumShopController;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopCart;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

final class PremiumShopCartQuantityTest extends TestCase
{
    private PremiumShopController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.cart_quantity_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'database.default' => 'cart_quantity_test']);
        DB::purge('cart_quantity_test');
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->text('name')->nullable();
        });
        Schema::create('shop_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('structure_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('price');
            $table->integer('diamond');
        });
        Schema::create('shop_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shop_item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('quantity');
        });
        Schema::create('shop_carts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_item_id');
            $table->integer('quantity');
            $table->timestamps();
        });
        DB::table('share_items')->insert(['id' => 1, 'name' => '{"ru":"Товар"}']);
        DB::table('shop_items')->insert([
            ['id' => 11, 'structure_id' => 10, 'share_item_id' => 1, 'price' => 100, 'diamond' => 3],
            ['id' => 12, 'structure_id' => 10, 'share_item_id' => 1, 'price' => 200, 'diamond' => 5],
            ['id' => 13, 'structure_id' => 19, 'share_item_id' => 1, 'price' => 10, 'diamond' => 0],
        ]);
        DB::table('shop_carts')->insert([
            ['id' => 1, 'user_id' => 1, 'shop_item_id' => 11, 'quantity' => 1],
            ['id' => 2, 'user_id' => 1, 'shop_item_id' => 12, 'quantity' => 2],
            ['id' => 3, 'user_id' => 1, 'shop_item_id' => 13, 'quantity' => 1],
            ['id' => 4, 'user_id' => 2, 'shop_item_id' => 11, 'quantity' => 8],
        ]);
        $this->controller = new PremiumShopController(
            new ShopCartService,
            Mockery::mock(ItemTooltipCollector::class),
            Mockery::mock(PurchaseCart::class),
            Mockery::mock(GetShopItems::class),
        );
    }

    protected function tearDown(): void
    {
        DB::purge('cart_quantity_test');
        parent::tearDown();
    }

    public function test_quantity_is_replaced_and_line_and_cart_totals_are_recalculated(): void
    {
        $response = $this->controller->updateCart($this->request(11, 5))->getData(true);

        self::assertSame(5, $response['quantity']);
        self::assertSame('500', $response['line_price']);
        self::assertSame('15', $response['line_diamond']);
        self::assertSame('900', $response['total_price']);
        self::assertSame('25', $response['total_diamond']);
        self::assertSame(5, (int) ShopCart::findOrFail(1)->quantity);
        self::assertSame(8, (int) ShopCart::findOrFail(4)->quantity);

        $this->controller->updateCart($this->request(11, 5));
        self::assertSame(5, (int) ShopCart::findOrFail(1)->quantity);
    }

    public function test_another_shop_cannot_be_updated_through_the_premium_endpoint(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->controller->updateCart($this->request(13, 5));
    }

    public function test_another_players_cart_cannot_be_changed(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $request = $this->request(12, 5);
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => 2]));
        $this->controller->updateCart($request);
    }

    public function test_invalid_quantities_are_rejected_without_changing_the_cart(): void
    {
        foreach ([0, -1, 10001, 'abc', '1.5'] as $quantity) {
            try {
                $this->controller->updateCart($this->request(11, $quantity));
                self::fail('Invalid quantity must be rejected.');
            } catch (ValidationException) {
                self::assertSame(1, (int) ShopCart::findOrFail(1)->quantity);
            }
        }
    }

    private function request(int $itemId, int|string $quantity): Request
    {
        $request = Request::create('/premium/update-cart', 'POST', ['shop_item_id' => $itemId, 'quantity' => $quantity]);
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => 1]));

        return $request;
    }
}
