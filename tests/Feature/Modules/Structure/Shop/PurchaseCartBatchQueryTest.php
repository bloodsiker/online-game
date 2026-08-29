<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Shop;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Shop\Application\DTOs\ShopCartDTO;
use App\Modules\Structure\Shop\Application\Services\ShopCartService;
use App\Modules\Structure\Shop\Application\UseCases\PurchaseCart;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopCart;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PurchaseCartBatchQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('money')->default(0);
            $table->unsignedInteger('diamond')->default(0);
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('upgrade_lvl')->default(0);
            $table->unsignedInteger('upgrade_pity')->default(0);
            $table->unsignedInteger('upgrade_fail_streak')->default(0);
            $table->unsignedInteger('socket_count')->default(0);
            $table->unsignedInteger('rune_slot_count')->default(0);
            $table->integer('additional_attack')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('equipped')->default(false);
            $table->unsignedInteger('count')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function test_existing_stacks_are_selected_once_for_the_whole_cart(): void
    {
        $cartItems = new Collection;
        for ($id = 1; $id <= 5; $id++) {
            $cartItems->push($this->cartItem($id));
        }

        $cartService = Mockery::mock(ShopCartService::class);
        $cartService->shouldReceive('getCart')->once()->andReturn(new ShopCartDTO($cartItems, 0, 0));
        $cartService->shouldReceive('clearCart')->once()->andReturn(5);

        $user = $this->user(10);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $result = (new PurchaseCart($cartService))->execute($user, 100);

        $backpackSelects = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains(strtolower($query), 'backpacks'));

        $this->assertTrue($result->ok);
        $this->assertCount(1, $backpackSelects);
        $this->assertSame(5, DB::table('backpacks')->where('user_id', 10)->count());
    }

    public function test_premium_shop_never_uses_another_players_stack(): void
    {
        DB::table('items')->insert(['id' => 50, 'share_item_id' => 1]);
        DB::table('backpacks')->insert([
            'user_id' => 20,
            'item_id' => 50,
            'count' => 7,
        ]);

        $cartService = Mockery::mock(ShopCartService::class);
        $cartService->shouldReceive('getCart')->once()->andReturn(new ShopCartDTO(
            new Collection([$this->cartItem(1, quantity: 2)]),
            0,
            0,
        ));
        $cartService->shouldReceive('clearCart')->once()->andReturn(1);

        $result = (new \App\Modules\Structure\PremiumShop\Application\UseCases\PurchaseCart($cartService))
            ->execute($this->user(10), 100);

        $this->assertTrue($result->ok);
        $this->assertSame(7, (int) DB::table('backpacks')->where('user_id', 20)->value('count'));
        $this->assertSame(1, DB::table('backpacks')->where('user_id', 10)->count());
        $this->assertSame(2, (int) DB::table('backpacks')->where('user_id', 10)->value('count'));
    }

    public function test_purchase_consumes_multiple_item_requirements(): void
    {
        DB::table('items')->insert([
            ['id' => 101, 'share_item_id' => 501],
            ['id' => 102, 'share_item_id' => 502],
        ]);
        DB::table('backpacks')->insert([
            ['user_id' => 10, 'item_id' => 101, 'count' => 5],
            ['user_id' => 10, 'item_id' => 102, 'count' => 9],
        ]);

        $firstPaymentItem = $this->shareItem(501, 'Первый ресурс');
        $secondPaymentItem = $this->shareItem(502, 'Второй ресурс');
        $cartService = Mockery::mock(ShopCartService::class);
        $cartService->shouldReceive('getCart')->once()->andReturn(new ShopCartDTO(
            new Collection([$this->cartItem(1, quantity: 2)]),
            0,
            0,
            [
                ['item' => $firstPaymentItem, 'quantity' => 4],
                ['item' => $secondPaymentItem, 'quantity' => 6],
            ],
        ));
        $cartService->shouldReceive('clearCart')->once()->andReturn(1);

        $result = (new PurchaseCart($cartService))->execute($this->user(10), 100);

        $this->assertTrue($result->ok);
        $this->assertSame(1, (int) DB::table('backpacks')->where('item_id', 101)->value('count'));
        $this->assertSame(3, (int) DB::table('backpacks')->where('item_id', 102)->value('count'));
        $this->assertSame(2, (int) DB::table('backpacks')
            ->join('items', 'items.id', '=', 'backpacks.item_id')
            ->where('backpacks.user_id', 10)
            ->where('items.share_item_id', 1)
            ->value('backpacks.count'));
    }

    private function cartItem(int $id, int $quantity = 1): ShopCart
    {
        $shareItem = $this->shareItem($id);

        $shopItem = (new ShopItem)->forceFill(['id' => $id]);
        $shopItem->exists = true;
        $shopItem->setRelation('item', $shareItem);

        $cartItem = (new ShopCart)->forceFill([
            'id' => $id,
            'quantity' => $quantity,
        ]);
        $cartItem->exists = true;
        $cartItem->setRelation('shopItem', $shopItem);

        return $cartItem;
    }

    private function shareItem(int $id, string $name = 'Предмет'): ShareItem
    {
        $shareItem = (new ShareItem)->forceFill([
            'id' => $id,
            'name' => $name,
            'type' => ShareItemType::RESOURCE,
            'is_stackable' => true,
            'count_use' => 0,
        ]);
        $shareItem->exists = true;

        return $shareItem;
    }

    private function user(int $id): User
    {
        DB::table('users')->insert([
            'id' => $id,
            'money' => 100,
            'diamond' => 100,
        ]);

        $user = (new User)->forceFill([
            'id' => $id,
            'money' => 100,
            'diamond' => 100,
        ]);
        $user->exists = true;
        $user->syncOriginal();

        return $user;
    }
}
