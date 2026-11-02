<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Commerce\Application\Services\PurchaseLogger;
use App\Modules\Commerce\Application\Services\ShopPurchaseLimitService;
use App\Modules\Commerce\Domain\Exceptions\PurchaseLimitExceeded;
use App\Modules\Commerce\Presentation\Http\Requests\ShopPurchaseLimitInput;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Reputation\Application\DTOs\ReputationShopCartDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Application\Services\ReputationShopCartService;
use App\Modules\Reputation\Application\UseCases\PurchaseReputationCart;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopCart;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\Reputation\Presentation\Http\ReputationController;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Shop\Application\Services\StructureShopAccessService;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopCart;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Database\Seeders\MoveTreasureHunterShopToStructureSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShopPurchaseLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('money')->default(100);
            $table->timestamps();
        });
        foreach (['shop_items', 'reputation_shop_items', 'influence_shop_items'] as $name) {
            Schema::create($name, fn (Blueprint $table) => $table->id());
        }
        (require database_path('migrations/2026_11_02_000001_add_shop_purchase_limits.php'))->up();
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        $this->freezeTime();
    }

    private function offer(int $quantity = 1, int $period = 604800): ShopItem
    {
        return (new ShopItem)->forceFill(['id' => 10, 'purchase_limit_quantity' => $quantity, 'purchase_limit_period_seconds' => $period])
            ->setRelation('item', (new ShareItem)->forceFill(['name' => ['ru' => 'Карта', 'uk' => 'Карта']]));
    }

    private function user(int $id = 1): User
    {
        return User::without('player')->findOrFail($id);
    }

    private function reserve(ShopItem $offer, int $quantity = 1, int $user = 1): void
    {
        DB::transaction(fn () => app(ShopPurchaseLimitService::class)->reserve($this->user($user), $offer, $quantity));
    }

    public function test_weekly_limit_blocks_repeat_purchase_until_exact_expiry(): void
    {
        $offer = $this->offer();
        $this->reserve($offer);
        $this->travel(7 * 86400 - 1)->seconds();
        try {
            $this->reserve($offer);
            $this->fail('A purchase was allowed before the cooldown ended.');
        } catch (PurchaseLimitExceeded) {
            $this->assertDatabaseHas('shop_purchase_limit_states', ['user_id' => 1, 'quantity' => 1]);
        }
        $this->travel(1)->seconds();
        $this->reserve($offer);
        $this->assertDatabaseCount('shop_purchase_limit_states', 1);
        $this->assertDatabaseHas('shop_purchase_limit_states', ['user_id' => 1, 'period_started_at' => now()->toDateTimeString(), 'quantity' => 1]);
    }

    public function test_five_items_can_be_bought_in_parts_without_extending_the_period(): void
    {
        $offer = $this->offer(5);
        $started = now()->toDateTimeString();
        $this->reserve($offer, 2);
        $this->travel(1)->days();
        $this->reserve($offer, 3);
        $this->assertDatabaseHas('shop_purchase_limit_states', ['quantity' => 5, 'period_started_at' => $started]);
        $this->expectException(PurchaseLimitExceeded::class);
        $this->reserve($offer);
    }

    public function test_limit_is_per_player_and_per_offer_and_independent_between_shop_types(): void
    {
        $service = app(ShopPurchaseLimitService::class);
        $offer = $this->offer();
        $this->reserve($offer);
        $this->reserve($offer, user: 2);
        $other = $this->offer()->forceFill(['id' => 11]);
        $this->reserve($other);
        $reputationOffer = (new ReputationShopItem)->forceFill($offer->getAttributes())->setRelation('item', $offer->item);
        DB::transaction(fn () => $service->reserve($this->user(), $reputationOffer, 1));
        $this->assertDatabaseCount('shop_purchase_limit_states', 4);
    }

    public function test_failed_delivery_rolls_back_payment_and_every_cart_limit(): void
    {
        $service = app(ShopPurchaseLimitService::class);
        $first = $this->offer();
        $second = $this->offer()->forceFill(['id' => 11]);
        $this->reserve($second);
        try {
            DB::transaction(function () use ($service, $first, $second): void {
                $user = $this->user();
                $user->decrement('money', 10);
                $service->reserve($user, $first, 1);
                $service->reserve($user, $second, 1);
            });
            $this->fail('The whole cart must fail.');
        } catch (PurchaseLimitExceeded) {
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
            $this->assertDatabaseMissing('shop_purchase_limit_states', ['offer_id' => 10]);
            $this->assertDatabaseHas('shop_purchase_limit_states', ['offer_id' => 11, 'quantity' => 1]);
        }
    }

    public function test_oversized_first_purchase_does_not_start_cooldown_and_unlimited_offers_have_no_state(): void
    {
        try {
            $this->reserve($this->offer(5), 6);
            $this->fail('Cannot buy more than the limit.');
        } catch (PurchaseLimitExceeded) {
            $this->assertDatabaseCount('shop_purchase_limit_states', 0);
        }
        $this->reserve($this->offer(0), 100);
        $this->assertDatabaseCount('shop_purchase_limit_states', 0);
    }

    public function test_reputation_checkout_reserves_limit_and_rejects_replayed_cart_without_payment_or_delivery(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id')->nullable();
            $table->integer('diamond')->default(0);
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        DB::table('players')->insert(['id' => 1, 'user_id' => 1]);
        DB::table('users')->where('id', 1)->update(['player_id' => 1]);
        $reputation = (new Reputation)->forceFill(['id' => 35, 'name' => ['ru' => 'Кладоискатель']])->setRelation('npc', null);
        $offer = (new ReputationShopItem)->forceFill(['id' => 10, 'price' => 10, 'diamond' => 0, 'min_points' => 0, 'purchase_limit_quantity' => 1, 'purchase_limit_period_seconds' => 604800])
            ->setRelation('item', $this->offer()->item)->setRelation('requirements', collect());
        $line = (new ReputationShopCart)->forceFill(['quantity' => 1])->setRelation('shopItem', $offer);
        $cart = new ReputationShopCartDTO(new Collection([$line]), 0, 10);
        $repository = \Mockery::mock(ReputationReadRepository::class);
        $repository->shouldReceive('findReputationForShopOrFail')->andReturn($reputation);
        $reputations = \Mockery::mock(ReputationService::class);
        $reputations->shouldReceive('getOrZero')->andReturn((new PlayerReputation)->forceFill(['points' => 0]));
        $carts = \Mockery::mock(ReputationShopCartService::class);
        $carts->shouldReceive('getCart')->andReturn($cart);
        $carts->shouldReceive('clearCart')->once()->andReturn(1);
        $backpack = \Mockery::mock(BackpackService::class);
        $backpack->shouldReceive('addItemByShareItem')->once()->andReturn(new Backpack);
        $logger = \Mockery::mock(PurchaseLogger::class);
        $logger->shouldReceive('record')->once()->andReturn('test-purchase');
        $checkout = new PurchaseReputationCart($repository, $reputations, $carts, $backpack, $logger);

        $this->assertTrue($checkout->execute($this->user(), 35)->ok);
        $this->assertFalse($checkout->execute($this->user(), 35)->ok);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 90]);
        $this->assertDatabaseHas('shop_purchase_limit_states', ['offer_type' => 'reputation_shop_items', 'quantity' => 1]);
    }

    public function test_admin_limit_input_converts_days_to_seconds_and_rejects_a_missing_period(): void
    {
        $input = Request::create('/', 'POST', ['purchase_limit_quantity' => 5, 'purchase_limit_period_days' => 7]);
        $this->assertSame(['purchase_limit_quantity' => 5, 'purchase_limit_period_seconds' => 604800], ShopPurchaseLimitInput::from($input));
        $this->expectException(ValidationException::class);
        ShopPurchaseLimitInput::from(Request::create('/', 'POST', ['purchase_limit_quantity' => 5]));
    }

    public function test_treasure_shop_moves_to_a_structure_without_resetting_weekly_limits_or_carts(): void
    {
        Schema::create('locations', fn (Blueprint $table) => $table->id());
        Schema::create('npcs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('reputations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('npc_id');
        });
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->json('name');
            $table->json('description');
            $table->unsignedBigInteger('npc_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamps();
        });
        Schema::create('share_structure_categories', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
        });
        foreach (['reputation_categories' => 'reputation_id', 'shop_categories' => 'structure_id'] as $table => $foreign) {
            Schema::create($table, function (Blueprint $table) use ($foreign): void {
                $table->unsignedBigInteger($foreign);
                $table->unsignedBigInteger('share_structure_category_id');
            });
        }
        foreach (['shop_items' => 'structure_id', 'reputation_shop_items' => 'reputation_id'] as $table => $foreign) {
            Schema::table($table, function (Blueprint $table) use ($foreign): void {
                $table->unsignedBigInteger($foreign);
                $table->unsignedBigInteger('share_item_id');
                $table->unsignedBigInteger('share_structure_category_id');
                $table->integer('price')->default(0);
                $table->integer('diamond')->default(0);
                $table->integer('min_points')->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
        foreach (['shop_item_requirements' => 'shop_item_id', 'reputation_shop_item_requirements' => 'reputation_shop_item_id'] as $name => $foreign) {
            Schema::create($name, function (Blueprint $table) use ($foreign): void {
                $table->id();
                $table->unsignedBigInteger($foreign);
                $table->unsignedBigInteger('share_item_id');
                $table->integer('quantity');
                $table->timestamps();
            });
        }
        foreach (['shop_carts' => 'shop_item_id', 'reputation_shop_carts' => 'reputation_shop_item_id'] as $name => $foreign) {
            Schema::create($name, function (Blueprint $table) use ($foreign): void {
                $table->id();
                $table->unsignedBigInteger($foreign);
                $table->unsignedBigInteger('user_id');
                $table->integer('quantity');
                $table->timestamps();
            });
        }
        (require database_path('migrations/2026_11_02_000002_link_structure_shops_to_reputations.php'))->up();
        DB::table('locations')->insert(['id' => 4]);
        DB::table('npcs')->insert(['id' => 85, 'location_id' => 4]);
        DB::table('reputations')->insert(['id' => 35, 'npc_id' => 85]);
        DB::table('share_structure_categories')->insert(['id' => 23, 'name' => json_encode(['ru' => 'Карты сокровищ'])]);
        DB::table('reputation_categories')->insert(['reputation_id' => 35, 'share_structure_category_id' => 23]);
        DB::table('reputation_shop_items')->insert(['id' => 11, 'reputation_id' => 35, 'share_item_id' => 1961, 'share_structure_category_id' => 23, 'purchase_limit_quantity' => 1, 'purchase_limit_period_seconds' => 604800]);
        DB::table('shop_purchase_limit_states')->insert(['user_id' => 1, 'offer_type' => 'reputation_shop_items', 'offer_id' => 11, 'period_started_at' => now(), 'quantity' => 1]);
        DB::table('reputation_shop_carts')->insert(['user_id' => 1, 'reputation_shop_item_id' => 11, 'quantity' => 1]);

        (new MoveTreasureHunterShopToStructureSeeder)->run();
        $shop = Structure::where('reputation_id', 35)->sole();
        $offer = ShopItem::where('structure_id', $shop->id)->sole();
        $this->assertSame('shop', $shop->type);
        $this->assertSame(85, $shop->npc_id);
        $this->assertDatabaseHas('shop_categories', ['structure_id' => $shop->id, 'share_structure_category_id' => 23]);
        $this->assertDatabaseHas('shop_carts', ['user_id' => 1, 'shop_item_id' => $offer->id, 'quantity' => 1]);
        $this->assertDatabaseCount('reputation_shop_items', 0);
        $this->assertDatabaseCount('reputation_shop_carts', 0);
        $this->assertSame(0, app(ShopPurchaseLimitService::class)->availability($this->user(), $offer)['remaining']);
        (new MoveTreasureHunterShopToStructureSeeder)->run();
        $this->assertDatabaseCount('structures', 1);
        $this->assertDatabaseCount('shop_items', 1);
        $this->assertDatabaseCount('shop_purchase_limit_states', 1);
        $redirect = app(ReputationController::class)->shop(35, Request::create('/reputation/35/shop', 'GET', ['category_id' => 23]));
        $this->assertSame(route('shop', ['id' => $shop->id, 'category_id' => 23]), $redirect->getTargetUrl());
    }

    public function test_structure_shop_preserves_npc_location_and_reputation_requirements(): void
    {
        Schema::create('player_reputations', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('reputation_id');
            $table->integer('points');
        });
        $shop = (new Structure)->forceFill(['reputation_id' => 35, 'npc_id' => 85])
            ->setRelation('npc', (new Npc)->forceFill(['location_id' => 4]));
        $user = $this->user()->forceFill(['player_id' => 1, 'location_id' => 4]);
        $line = (new ShopCart)->setRelation('shopItem', $this->offer()->forceFill(['min_reputation_points' => 100]));
        $access = new StructureShopAccessService;
        DB::table('player_reputations')->insert(['player_id' => 1, 'reputation_id' => 35, 'points' => 99]);
        $this->assertNotNull($access->purchaseError($user, $shop, [$line]));
        DB::table('player_reputations')->update(['points' => 100]);
        $this->assertNull($access->purchaseError($user, $shop, [$line]));
        $user->location_id = 5;
        $this->assertNotNull($access->purchaseError($user, $shop, [$line]));
    }
}
