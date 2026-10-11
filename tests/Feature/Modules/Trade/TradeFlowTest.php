<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Trade;

use App\Modules\Backpack\Domain\DTO\BackpackDTO;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Trade\Application\Services\TradeAdminService;
use App\Modules\Trade\Application\Services\TradeService;
use App\Modules\Trade\Domain\Services\TradeBalance;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TradeFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->schema();
        DB::table('players')->insert(['id' => 1, 'hp_now' => 100]);
        DB::table('users')->insert(['id' => 1, 'player_id' => 1, 'location_id' => 2, 'money' => 10000]);
        DB::table('npcs')->insert([['id' => 1, 'location_id' => 2, 'is_active' => 1], ['id' => 2, 'location_id' => 102, 'is_active' => 1]]);
        DB::table('skills')->insert(['id' => 1, 'name' => TradeBalance::SKILL_NAME, 'type' => 'peaceful']);
        for ($level = 1; $level <= 300; $level++) {
            DB::table('skill_level_requirements')->insert(['skill_id' => 1, 'lvl' => $level, 'exp_required' => TradeBalance::levelThreshold($level), 'exp_diff' => 100 + 5 * ($level - 1)]);
        }
        DB::table('trade_posts')->insert([['id' => 1, 'npc_id' => 1, 'name' => 'Город', 'license_price' => 100], ['id' => 2, 'npc_id' => 2, 'name' => 'Лес', 'license_price' => 300]]);
        DB::table('share_items')->insert(['id' => 1, 'name' => 'Холстина', 'type' => 'trade_goods', 'is_stackable' => 1, 'is_active' => 1]);
        DB::table('trade_goods')->insert(['id' => 1, 'share_item_id' => 1, 'trade_post_id' => 1, 'base_price' => 40]);
        DB::table('trade_post_stocks')->insert(['trade_good_id' => 1, 'last_restock_at' => now()]);
        DB::table('trade_routes')->insert(['trade_post_a_id' => 1, 'trade_post_b_id' => 2, 'distance_tier' => 'near', 'exp_per_unit' => 5]);
        DB::table('trade_buy_offers')->insert(['trade_post_id' => 2, 'trade_good_id' => 1, 'buy_price' => 42]);
    }

    public function test_full_trade_flow_and_idempotent_requests(): void
    {
        $license = (string) Str::uuid();
        $this->act(1, 'license', null, 1, $license);
        $this->act(1, 'license', null, 1, $license);
        $key = (string) Str::uuid();
        $this->act(1, 'purchase', 1, 10, $key);
        $this->act(1, 'purchase', 1, 10, $key);
        $this->assertSame(9500, (int) DB::table('users')->value('money'));
        $this->assertSame(20, (int) DB::table('trade_post_stocks')->value('current_quantity'));
        $this->assertSame(10, (int) DB::table('backpacks')->sum('count'));
        DB::table('users')->update(['location_id' => 102]);
        $this->act(2, 'license', null, 1);
        $sale = (string) Str::uuid();
        $this->act(2, 'sale', 1, 10, $sale);
        $this->act(2, 'sale', 1, 10, $sale);
        $this->assertSame(9620, (int) DB::table('users')->value('money'));
        $this->assertSame(0, DB::table('backpacks')->count());
        $this->assertSame(0, DB::table('items')->count());
        $this->assertSame(50, (int) DB::table('player_skills')->value('exp'));
        $this->assertSame(4, DB::table('trade_logs')->count());
    }

    public function test_failed_stock_purchase_rolls_back_money_and_inventory(): void
    {
        $this->act(1, 'license', null, 1);
        DB::table('trade_post_stocks')->update(['current_quantity' => 1]);
        try {
            $this->act(1, 'purchase', 1, 2);
            $this->fail('Expected validation error');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('раскуплен', $exception->getMessage());
        }
        $this->assertSame(9900, (int) DB::table('users')->value('money'));
        $this->assertSame(0, DB::table('backpacks')->count());
        $this->assertSame(1, DB::table('trade_logs')->count());
    }

    public function test_location_license_skill_and_capacity_guards(): void
    {
        foreach (['license', 'purchase', 'skill', 'capacity', 'dead', 'battle'] as $guard) {
            DB::table('users')->update(['location_id' => 2]);
            DB::table('players')->update(['hp_now' => 100]);
            DB::table('battle_details')->delete();
            DB::table('trade_goods')->update(['required_skill_lvl' => 1]);
            DB::table('player_trade_licenses')->delete();
            if ($guard !== 'purchase') {
                DB::table('player_trade_licenses')->insert(['player_id' => 1, 'trade_post_id' => 1, 'purchased_at' => now()]);
            }
            if ($guard === 'license') {
                DB::table('users')->update(['location_id' => 999]);
            }
            if ($guard === 'skill') {
                DB::table('trade_goods')->update(['required_skill_lvl' => 50]);
            }
            if ($guard === 'dead') {
                DB::table('players')->update(['hp_now' => 0]);
            }
            if ($guard === 'battle') {
                DB::table('battles')->insert(['id' => 1, 'location_id' => 2, 'status' => 1]);
                DB::table('battle_details')->insert(['battle_id' => 1, 'user_id' => 1, 'status' => 1]);
            }
            try {
                $this->act(1, 'purchase', 1, $guard === 'capacity' ? 11 : 1);
                $this->fail('Missing guard '.$guard);
            } catch (ValidationException) {
                $this->assertSame(10000, (int) DB::table('users')->value('money'));
            }
        }
    }

    public function test_destination_license_required_to_sell(): void
    {
        $this->act(1, 'license', null, 1);
        $this->act(1, 'purchase', 1, 1);
        DB::table('users')->update(['location_id' => 102]);
        $this->expectException(ValidationException::class);
        $this->act(2, 'sale', 1, 1);
    }

    public function test_battle_on_another_location_does_not_block_trade(): void
    {
        DB::table('battles')->insert(['id' => 1, 'location_id' => 102, 'status' => 1]);
        DB::table('battle_details')->insert(['battle_id' => 1, 'user_id' => 1, 'status' => 1]);
        $this->act(1, 'license', null, 1);
        $this->act(1, 'purchase', 1, 1);
        $this->assertSame(9860, (int) DB::table('users')->value('money'));
    }

    public function test_finished_battle_on_current_location_does_not_block_trade(): void
    {
        DB::table('battles')->insert(['id' => 1, 'location_id' => 2, 'status' => 0]);
        DB::table('battle_details')->insert(['battle_id' => 1, 'user_id' => 1, 'status' => 1]);
        $this->act(1, 'license', null, 1);
        $this->assertSame(9900, (int) DB::table('users')->value('money'));
    }

    public function test_lazy_restock_and_render_trade_page(): void
    {
        DB::table('trade_post_stocks')->update(['current_quantity' => 0, 'last_restock_at' => now()->subMinutes(65)]);
        $page = app(TradeService::class)->page(User::query()->findOrFail(1), 1);
        $this->assertSame(10, (int) $page['goods']->first()->stock->current_quantity);
        $this->assertStringContainsString('Холстина', view('trade::index', $page + ['errors' => new ViewErrorBag])->render());
        $this->assertSame(10, TradeBalance::capacity(1));
        $this->assertSame(30, TradeBalance::capacity(300));
        $this->assertSame(42, TradeBalance::salePrice(40, 42, 1));
        $this->assertSame(46, TradeBalance::salePrice(40, 42, 300));
    }

    public function test_admin_cannot_reprice_existing_cargo(): void
    {
        $this->expectException(ValidationException::class);
        app(TradeAdminService::class)->save('goods', ['id' => 1, 'share_item_id' => 1, 'trade_post_id' => 1, 'base_price' => 20, 'required_skill_lvl' => 1, 'sort_order' => 0, 'is_active' => 1]);
    }

    public function test_sale_section_only_displays_goods_in_backpack(): void
    {
        DB::table('users')->update(['location_id' => 102]);
        $page = app(TradeService::class)->page(User::query()->findOrFail(1), 2);
        $this->assertStringNotContainsString('Холстина', view('trade::index', $page + ['errors' => new ViewErrorBag, 'activeTab' => 'sale'])->render());

        DB::table('users')->update(['location_id' => 2]);
        $this->act(1, 'license', null, 1);
        $this->act(1, 'purchase', 1, 1);
        DB::table('users')->update(['location_id' => 102]);
        $page = app(TradeService::class)->page(User::query()->findOrFail(1), 2);
        $this->assertStringContainsString('Холстина', view('trade::index', $page + ['errors' => new ViewErrorBag, 'activeTab' => 'sale'])->render());
    }

    public function test_npc_trade_entry_uses_shop_row_design(): void
    {
        $html = view('trade::npc-shop', ['post' => (object) ['id' => 1, 'name' => 'Нейрин']])->render();
        $this->assertStringContainsString('qst_store.gif', $html);
        $this->assertStringContainsString('Вольный торг: Нейрин', $html);
        $this->assertStringContainsString('value="Далее"', $html);
        $this->assertStringContainsString(Js::from(route('trade.show', 1))->toHtml(), $html);
    }

    public function test_tabs_show_only_the_selected_trade_section(): void
    {
        $page = app(TradeService::class)->page(User::query()->findOrFail(1), 1);
        foreach (['purchase', 'sale'] as $tab) {
            $html = view('trade::index', $page + ['errors' => new ViewErrorBag, 'activeTab' => $tab])->render();
            $this->assertStringContainsString('Купить товар', $html);
            $this->assertStringContainsString('Продать товар', $html);
            $this->assertStringContainsString('data-trade-section="'.$tab.'"', $html);
            $this->assertStringNotContainsString('data-trade-section="'.($tab === 'sale' ? 'purchase' : 'sale').'"', $html);
        }
    }

    public function test_skill_progress_uses_experience_within_current_level(): void
    {
        $user = User::query()->findOrFail(1);
        $service = app(TradeService::class);
        $this->assertEquals(0, $service->page($user, 1)['skillProgress']);
        DB::table('player_skills')->insert([
            'player_id' => 1, 'skill_id' => 1, 'lvl' => 2,
            'exp' => 150, 'exp_up' => 205, 'exp_diff' => 105,
        ]);
        $page = $service->page($user, 1);
        $this->assertEquals(47.62, $page['skillProgress']);
        $this->assertSame('Опыт: 50 / 105', $page['skillProgressTitle']);
        DB::table('player_skills')->update(['lvl' => 300]);
        $page = $service->page($user, 1);
        $this->assertEquals(100, $page['skillProgress']);
        $this->assertSame('Максимальный уровень навыка', $page['skillProgressTitle']);
    }

    public function test_stock_limit_migration_caps_stock_without_replenishing_sold_goods(): void
    {
        $migration = require database_path('migrations/2026_09_30_000002_reduce_trade_stock_to_fifteen.php');
        $migration->up();
        $this->assertSame(15, (int) DB::table('trade_post_stocks')->value('current_quantity'));
        $this->assertSame(15, (int) DB::table('trade_post_stocks')->value('max_quantity'));
        DB::table('trade_post_stocks')->update(['current_quantity' => 4]);
        $migration->up();
        $this->assertSame(4, (int) DB::table('trade_post_stocks')->value('current_quantity'));
        DB::table('trade_post_stocks')->update(['last_restock_at' => now()->subDays(1)]);
        $page = app(TradeService::class)->page(User::query()->findOrFail(1), 1);
        $this->assertSame(15, (int) $page['goods']->first()->stock->current_quantity);
    }

    public function test_reusing_request_key_for_another_quantity_is_rejected(): void
    {
        $this->act(1, 'license', null, 1);
        $key = (string) Str::uuid();
        $this->act(1, 'purchase', 1, 1, $key);
        $this->expectException(ValidationException::class);
        $this->act(1, 'purchase', 1, 2, $key);
    }

    public function test_trade_goods_are_grouped_separately_in_backpack(): void
    {
        $this->act(1, 'license', null, 1);
        $this->act(1, 'purchase', 1, 2);
        $backpack = Backpack::query()->with('item')->get();
        $groups = app(BackpackService::class)->groupItemsByType($backpack);
        $dto = new BackpackDTO($backpack, $groups, 0, 'main');
        $this->assertSame(2, (int) $dto->getTradeGoods()->first()->count);
        $this->assertContains(ShareItemType::TRADE_GOODS, ShareItemType::group('main'));
    }

    private function act(int $post, string $action, ?int $good, int $quantity, ?string $key = null): array
    {
        return app(TradeService::class)->act(User::query()->findOrFail(1), $post, $action, $good, $quantity, $key ?? (string) Str::uuid());
    }

    private function schema(): void
    {
        Schema::create('players', function (Blueprint $t): void {
            $t->id();
            $t->integer('hp_now');
            $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t): void {
            $t->id();
            $t->integer('player_id');
            $t->integer('location_id');
            $t->integer('money');
            $t->timestamps();
        });
        Schema::create('npcs', function (Blueprint $t): void {
            $t->id();
            $t->integer('location_id');
            $t->boolean('is_active');
        });
        Schema::create('npc_influence_requirements', function (Blueprint $t): void {
            $t->id();
            $t->integer('npc_id');
            $t->integer('map_id');
            $t->integer('required_influence');
        });
        Schema::create('skills', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('type');
        });
        Schema::create('skill_level_requirements', function (Blueprint $t): void {
            $t->id();
            $t->integer('skill_id');
            $t->integer('lvl');
            $t->integer('exp_required');
            $t->integer('exp_diff');
        });
        Schema::create('player_skills', function (Blueprint $t): void {
            $t->id();
            foreach (['player_id', 'skill_id', 'lvl', 'exp', 'exp_up', 'exp_diff'] as $c) {
                $t->integer($c);
            } $t->timestamps();
        });
        Schema::create('battles', function (Blueprint $t): void {
            $t->id();
            $t->integer('location_id');
            $t->integer('status');
        });
        Schema::create('battle_details', function (Blueprint $t): void {
            $t->id();
            $t->integer('battle_id');
            $t->integer('user_id');
            $t->integer('status');
        });
        Schema::create('share_items', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('type');
            $t->boolean('is_active');
            $t->boolean('is_stackable');
            $t->integer('count_use')->default(0);
        });
        Schema::create('items', function (Blueprint $t): void {
            $t->id();
            $t->integer('share_item_id');
            foreach (['upgrade_lvl', 'upgrade_pity', 'upgrade_fail_streak', 'socket_count', 'rune_slot_count', 'additional_attack', 'count_use', 'is_open'] as $c) {
                $t->integer($c)->default(0);
            } $t->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $t): void {
            $t->id();
            $t->integer('share_item_id');
        });
        Schema::create('backpacks', function (Blueprint $t): void {
            $t->id();
            $t->integer('user_id');
            $t->integer('item_id');
            $t->integer('equipped')->default(0);
            $t->integer('count');
            $t->timestamps();
        });
        $migration = require database_path('migrations/2026_09_30_000001_create_trade_system.php');
        $migration->up();
    }
}
