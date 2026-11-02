<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Application\UseCases\SharpenArtifact;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ArtifactUpgradeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        app()->setLocale('ru');
        $this->withoutMiddleware();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->decimal('money', 16, 2);
            $table->integer('diamond')->default(0);
            $table->unsignedBigInteger('location_id');
            $table->timestamps();
        });
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('location_id');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->string('rarity')->default('common');
        });
        Schema::create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->integer('value');
            $table->string('value_type');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            foreach (array_keys((new Item)->getAttributes()) as $attribute) {
                $table->integer($attribute)->default(0);
            }
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('count')->default(1);
            $table->boolean('equipped')->default(false);
            $table->timestamps();
        });
        Schema::create('player_trade_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('initiator_user_id');
            $table->unsignedBigInteger('target_user_id');
            $table->string('status');
            $table->timestamp('expires_at');
        });
        (require database_path('migrations/2026_10_25_000001_create_artifact_upgrade_configs.php'))->up();
        DB::table('users')->insert(['id' => 1, 'money' => 1000, 'location_id' => 10]);
        DB::table('structures')->insert(['id' => 8, 'type' => 'blacksmith', 'location_id' => 10]);
        DB::table('share_items')->insert([
            ['id' => 100, 'name' => '{"ru":"Артефакт"}', 'type' => 'artifact'],
            ['id' => 200, 'name' => '{"ru":"Кристалл"}', 'type' => 'resource'],
        ]);
        foreach ([1 => 100, 2 => 100, 3 => 200] as $id => $shareItemId) {
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $shareItemId, 'upgrade_lvl' => $id === 1 ? 12 : 0]);
            DB::table('backpacks')->insert(['id' => $id, 'user_id' => 1, 'item_id' => $id, 'count' => $id === 3 ? 10 : 1]);
        }
        DB::table('artifact_upgrade_configs')->insert(['id' => 1, 'share_item_id' => 100, 'is_enabled' => true, 'gold_cost' => 100]);
        DB::table('artifact_upgrade_materials')->insert(['config_id' => 1, 'share_item_id' => 200, 'count' => 3]);
        $this->actingAs(User::query()->setEagerLoads([])->findOrFail(1)->setRelation('player', null));
    }

    public function test_upgrade_preserves_target_and_consumes_exact_resources_with_fixed_price(): void
    {
        $this->post(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => 12])
            ->assertRedirect(route('blacksmith', ['id' => 8, 'section' => 'artifact-upgrade']))->assertSessionHasNoErrors()
            ->assertSessionMissing('message');
        $this->assertDatabaseHas('items', ['id' => 1, 'upgrade_lvl' => 13, 'share_item_id' => 100]);
        $this->assertDatabaseMissing('items', ['id' => 2]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 3, 'count' => 7]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 900]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 1, 'count' => 1]);
    }

    public function test_duplicate_submission_does_not_charge_twice(): void
    {
        $this->post(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => 12])->assertSessionHasNoErrors();
        $this->postJson(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => 12])->assertUnprocessable();
        $this->assertDatabaseHas('users', ['money' => 900]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 3, 'count' => 7]);
    }

    #[DataProvider('invalidResources')]
    public function test_invalid_resources_leave_inventory_and_money_unchanged(string $table, int $id, array $changes): void
    {
        DB::table($table)->where('id', $id)->update($changes);
        $money = DB::table('users')->value('money');
        $snapshot = DB::table('backpacks')->orderBy('id')->get()->toJson();
        $level = (int) DB::table('items')->where('id', 1)->value('upgrade_lvl');
        try {
            app(SharpenArtifact::class)->execute(auth()->user(), 8, 1, $level);
            $this->fail('Upgrade should be rejected.');
        } catch (ValidationException) {
            $this->assertSame($money, DB::table('users')->value('money'));
            $this->assertSame($snapshot, DB::table('backpacks')->orderBy('id')->get()->toJson());
            $this->assertDatabaseHas('items', ['id' => 1, 'upgrade_lvl' => $level]);
        }
    }

    public static function invalidResources(): array
    {
        return [
            'upgraded donor' => ['items', 2, ['upgrade_lvl' => 1]],
            'equipped donor' => ['backpacks', 2, ['equipped' => true]],
            'foreign donor' => ['backpacks', 2, ['user_id' => 2]],
            'wrong donor' => ['items', 2, ['share_item_id' => 200]],
            'expired donor' => ['items', 2, ['expires_at' => '2000-01-01 00:00:00']],
            'materials' => ['backpacks', 3, ['count' => 2]],
            'money' => ['users', 1, ['money' => 99]],
            'disabled' => ['artifact_upgrade_configs', 1, ['is_enabled' => false]],
            'limit' => ['items', 1, ['upgrade_lvl' => 25]],
            'equipped target' => ['backpacks', 1, ['equipped' => true]],
            'foreign target' => ['backpacks', 1, ['user_id' => 2]],
            'wrong location' => ['users', 1, ['location_id' => 11]],
        ];
    }

    public function test_only_copy_cannot_be_its_own_donor(): void
    {
        DB::table('items')->where('id', 1)->update(['upgrade_lvl' => 0]);
        DB::table('backpacks')->where('id', 2)->delete();
        $this->postJson(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => 0])->assertUnprocessable();
        $this->assertDatabaseHas('users', ['money' => 1000]);
    }

    public function test_active_trade_blocks_upgrade(): void
    {
        DB::table('player_trade_sessions')->insert(['initiator_user_id' => 1, 'target_user_id' => 2, 'status' => 'active', 'expires_at' => now()->addMinutes(10)]);
        $this->postJson(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => 12])->assertUnprocessable();
        $this->assertDatabaseHas('items', ['id' => 2]);
    }

    public function test_new_tab_renders_configured_artifacts_requirements_and_matching_heading(): void
    {
        $this->withoutExceptionHandling();
        $collector = Mockery::mock(ItemTooltipCollector::class);
        $collector->shouldReceive('collectFrom')->times(3)->andReturnSelf();
        $collector->shouldReceive('renderScript')->once()->andReturn('');
        $this->app->instance(ItemTooltipCollector::class, $collector);
        $this->get(route('blacksmith', ['id' => 8, 'section' => 'artifact-upgrade']))
            ->assertOk()->assertSee('Заточка артефактов')->assertSee('Артефакт +12')
            ->assertSee('expected_level', false)->assertSee('Кристалл')
            ->assertSee('<b>Кузня:</b> Заточка артефактов', false)
            ->assertSee('artifact-upgrade__equation', false)
            ->assertSee('artifact-upgrade__stats', false)
            ->assertSee('data-id="artifact_template_100"', false)
            ->assertSee('data-id="artifact_template_200"', false)
            ->assertSee('data-id="artifact_preview_1"', false)
            ->assertDontSee('<details', false)->assertDontSee('<summary', false);
    }

    public function test_admin_saves_price_and_materials_separately_from_rarity_upgrade(): void
    {
        $controller = app(ItemController::class);
        $item = ShareItem::findOrFail(100);
        $response = $controller->saveArtifactUpgrade(Request::create('/', 'POST', ['is_enabled' => 1, 'gold_cost' => 750]), $item);
        $this->assertSame(route('admin.item.info', 100).'#tab-artifact-upgrade', $response->getTargetUrl());
        $controller->addArtifactUpgradeMaterial(Request::create('/', 'POST', ['share_item_id' => 200, 'count' => 7]), $item);
        $this->assertDatabaseHas('artifact_upgrade_configs', ['share_item_id' => 100, 'gold_cost' => 750]);
        $this->assertDatabaseHas('artifact_upgrade_materials', ['config_id' => 1, 'share_item_id' => 200, 'count' => 7]);
        $controller->deleteArtifactUpgradeMaterial($item, ShareItem::findOrFail(200));
        $this->assertDatabaseMissing('artifact_upgrade_materials', ['config_id' => 1, 'share_item_id' => 200]);
    }

    public function test_zero_and_last_allowed_level_use_same_price(): void
    {
        foreach ([0, 24] as $level) {
            DB::table('items')->where('id', 1)->update(['upgrade_lvl' => $level]);
            DB::table('items')->insertOrIgnore(['id' => 2, 'share_item_id' => 100, 'upgrade_lvl' => 0]);
            DB::table('backpacks')->insertOrIgnore(['id' => 2, 'item_id' => 2, 'user_id' => 1]);
            $before = (float) DB::table('users')->value('money');
            $this->post(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => $level])->assertSessionHasNoErrors();
            $this->assertEquals($before - 100, DB::table('users')->value('money'));
            $this->assertDatabaseHas('items', ['id' => 1, 'upgrade_lvl' => $level + 1]);
        }
    }

    public function test_inventory_and_money_roll_back_if_saving_target_fails(): void
    {
        DB::unprepared("CREATE TRIGGER reject_artifact_upgrade BEFORE UPDATE ON items WHEN NEW.id = 1 BEGIN SELECT RAISE(ABORT, 'test rollback'); END");
        $this->postJson(route('blacksmith.artifact_upgrade', 8), ['item_id' => 1, 'expected_level' => 12])->assertStatus(500);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 1000]);
        $this->assertDatabaseHas('items', ['id' => 1, 'upgrade_lvl' => 12]);
        $this->assertDatabaseHas('items', ['id' => 2]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 2, 'count' => 1]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 3, 'count' => 10]);
    }
}
