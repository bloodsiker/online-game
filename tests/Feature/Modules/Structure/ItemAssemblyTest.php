<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Domain\Services\ItemRequirementService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\ItemAssembly\Application\Requests\SaveItemAssemblyRequest;
use App\Modules\ItemAssembly\Application\Services\AssembleItem;
use App\Modules\ItemAssembly\Application\Services\AssemblyCatalog;
use App\Modules\ItemAssembly\Application\Services\ManageItemAssembly;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\ItemAssembly\Presentation\Http\Admin\ItemAssemblyController;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetKraftPage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ItemAssemblyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->withoutMiddleware();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
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
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->integer('count_use')->default(0);
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
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->boolean('is_random_result')->default(false);
            $table->string('unlock_type')->default('single_use');
            $table->integer('percent')->default(100);
            $table->timestamps();
        });
        foreach (['share_recipe_has_items' => 'count', 'share_recipe_results' => 'chance'] as $name => $field) {
            Schema::create($name, function (Blueprint $table) use ($field): void {
                $table->id();
                $table->unsignedBigInteger('share_recipe_id');
                $table->unsignedBigInteger('share_item_id');
                $table->integer($field);
                $table->timestamps();
            });
        }
        (require database_path('migrations/2026_10_19_000002_create_item_assemblies.php'))->up();
        DB::table('users')->insert([['id' => 1, 'location_id' => 10], ['id' => 2, 'location_id' => 11]]);
        DB::table('structures')->insert(['id' => 8, 'type' => 'blacksmith', 'location_id' => 10]);
        foreach ([101, 102, 103, 104, 201, 202] as $id) {
            DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Предмет '.$id, 'uk' => 'Предмет '.$id]), 'type' => $id >= 200 ? 'artifact' : 'resource']);
            if ($id >= 200) {
                continue;
            }
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $id]);
            DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => $id, 'count' => 2]);
        }
        $this->actingAs((new User)->forceFill(['id' => 1, 'location_id' => 10])->setRelation('player', null));
    }

    private function createAssembly(array $overrides = []): ItemAssembly
    {
        return app(ManageItemAssembly::class)->save(new ItemAssembly, array_replace([
            'name' => 'Сборка', 'type' => 'artifact', 'result_mode' => 'random', 'is_active' => true,
            'components' => array_map(fn ($id) => ['share_item_id' => $id, 'count' => 1], [101, 102, 103, 104]),
            'rewards' => [['share_item_id' => 201, 'count' => 1, 'weight' => 10]],
        ], $overrides));
    }

    public function test_assembly_works_without_any_recipe_or_book_and_consumes_exact_quantities(): void
    {
        $assembly = $this->createAssembly();
        $url = route('blacksmith.assemble', ['id' => 8, 'assembly' => $assembly->id]);
        $this->postJson($url)->assertOk()->assertJsonPath('crafted_share_item_id', 201)->assertJsonPath('quantity', 1);
        foreach ([101, 102, 103, 104] as $id) {
            $this->assertDatabaseHas('backpacks', ['item_id' => $id, 'count' => 1]);
        }
        $this->assertDatabaseCount('share_recipes', 0);
        $this->postJson($url)->assertOk();
        $this->postJson($url)->assertUnprocessable();
        $this->assertSame(2, Item::where('share_item_id', 201)->count());
    }

    public function test_components_are_summed_and_consumed_across_multiple_stacks(): void
    {
        $assembly = $this->createAssembly(['components' => [['share_item_id' => 101, 'count' => 3]]]);
        DB::table('items')->insert(['id' => 301, 'share_item_id' => 101]);
        DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => 301, 'count' => 2]);
        $result = app(AssembleItem::class)->execute(auth()->user(), $assembly->id);
        $this->assertTrue($result->ok);
        $this->assertDatabaseMissing('backpacks', ['item_id' => 101]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 301, 'count' => 1]);
    }

    public function test_missing_or_equipped_components_do_not_consume_other_parts(): void
    {
        $assembly = $this->createAssembly();
        DB::table('backpacks')->where('item_id', 101)->update(['equipped' => true]);
        $result = app(AssembleItem::class)->execute(auth()->user(), $assembly->id);
        $this->assertFalse($result->ok);
        $this->assertDatabaseHas('backpacks', ['item_id' => 102, 'count' => 2]);
        $this->assertSame(0, Item::where('share_item_id', 201)->count());
    }

    public function test_disabled_assembly_and_remote_blacksmith_are_rejected(): void
    {
        $assembly = $this->createAssembly(['is_active' => false]);
        $this->postJson(route('blacksmith.assemble', ['id' => 8, 'assembly' => $assembly->id]))->assertUnprocessable();
        DB::table('users')->where('id', 1)->update(['location_id' => 11]);
        $this->postJson(route('blacksmith.assemble', ['id' => 8, 'assembly' => $assembly->id]))->assertForbidden();
        $this->assertDatabaseHas('backpacks', ['item_id' => 101, 'count' => 2]);
    }

    public function test_expired_components_are_not_counted_or_spent(): void
    {
        $assembly = $this->createAssembly();
        DB::table('items')->where('id', 101)->update(['expires_at' => now()->subMinute()]);
        $result = app(AssembleItem::class)->execute(auth()->user(), $assembly->id);
        $this->assertFalse($result->ok);
        $catalog = app(AssemblyCatalog::class)->execute(auth()->user(), AssemblyType::ARTIFACT);
        $this->assertSame(0, $catalog['assemblies'][0]['ingredients'][0]['availableCount']);
        $this->assertFalse($catalog['assemblies'][0]['canCraft']);
        $this->assertDatabaseHas('backpacks', ['item_id' => 102, 'count' => 2]);
    }

    public function test_reward_failure_rolls_back_consumed_components(): void
    {
        $assembly = $this->createAssembly();
        $backpack = \Mockery::mock(BackpackService::class, [app(ItemRequirementService::class)])->makePartial();
        $backpack->shouldReceive('giveItemsByShareItem')->once()->andThrow(new \RuntimeException('Reward failed'));
        try {
            (new AssembleItem($backpack))->execute(auth()->user(), $assembly->id);
            $this->fail('Expected reward failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Reward failed', $exception->getMessage());
        }
        foreach ([101, 102, 103, 104] as $id) {
            $this->assertDatabaseHas('backpacks', ['item_id' => $id, 'count' => 2]);
        }
        $this->assertSame(0, Item::where('share_item_id', 201)->count());
    }

    public function test_admin_forms_render_components_rewards_and_type_filter(): void
    {
        $assembly = $this->createAssembly();
        $controller = new ItemAssemblyController;
        $html = $controller->edit($assembly)->with('errors', new ViewErrorBag)->render();
        $this->assertStringContainsString('name="result_mode"', $html);
        $this->assertStringContainsString('name="components[0][share_item_id]"', $html);
        $this->assertStringContainsString('name="rewards[0][weight]"', $html);
        $this->assertStringContainsString('assembly-item-select', $html);
        $index = $controller->index(Request::create('/admin/item-assemblies'))->render();
        $this->assertStringContainsString('name="type"', $index);
        $this->assertStringContainsString('Сборка', $index);
    }

    public function test_fixed_result_can_be_used_for_non_artifact_assemblies(): void
    {
        $assembly = $this->createAssembly(['type' => 'consumable', 'result_mode' => 'fixed', 'rewards' => [['share_item_id' => 201, 'count' => 2, 'weight' => 77]]]);
        $result = app(AssembleItem::class)->execute(auth()->user(), $assembly->id);
        $this->assertTrue($result->ok);
        $this->assertSame(2, $result->count);
        $this->assertSame(2, Item::where('share_item_id', 201)->count());
        $this->assertSame(1, (int) $assembly->rewards()->first()->pivot->weight);
        $this->postJson(route('blacksmith.assemble', ['id' => 8, 'assembly' => $assembly->id]))->assertNotFound();
    }

    public function test_catalog_filters_types_and_aggregates_counts_without_recipes(): void
    {
        $this->createAssembly();
        $this->createAssembly(['type' => 'equipment']);
        $this->createAssembly(['is_active' => false]);
        $catalog = app(AssemblyCatalog::class)->execute(auth()->user(), AssemblyType::ARTIFACT);
        $this->assertCount(1, $catalog['assemblies']);
        $this->assertTrue($catalog['assemblies'][0]['canCraft']);
        $this->assertCount(4, $catalog['assemblies'][0]['ingredients']);
        $this->assertSame(2, $catalog['assemblies'][0]['ingredients'][0]['availableCount']);
    }

    public function test_admin_rejects_invalid_types_duplicate_components_and_bad_weights(): void
    {
        $data = ['name' => 'Сборка', 'type' => 'unknown', 'result_mode' => 'bad',
            'components' => [['share_item_id' => 101, 'count' => 0], ['share_item_id' => 101, 'count' => 1]],
            'rewards' => [['share_item_id' => 201, 'count' => 1, 'weight' => -1]]];
        $validator = Validator::make($data, (new SaveItemAssemblyRequest)->rules());
        $this->assertTrue($validator->fails());
        foreach (['type', 'result_mode', 'components.0.share_item_id', 'components.0.count', 'rewards.0.weight'] as $field) {
            $this->assertTrue($validator->errors()->has($field), $field);
        }
    }

    public function test_admin_does_not_allow_fixed_mode_with_multiple_rewards(): void
    {
        $this->expectException(ValidationException::class);
        $this->createAssembly(['result_mode' => 'fixed', 'rewards' => [['share_item_id' => 201, 'count' => 1], ['share_item_id' => 202, 'count' => 1]]]);
    }

    public function test_migration_preserves_item_ids_components_and_weights_and_can_restore_recipe(): void
    {
        DB::table('share_recipes')->insert(['id' => 145, 'share_item_id' => 104, 'percent' => 100, 'is_random_result' => true]);
        foreach ([101, 102, 103, 104] as $id) {
            DB::table('share_recipe_has_items')->insert(['share_recipe_id' => 145, 'share_item_id' => $id, 'count' => 1]);
        }
        DB::table('share_recipe_results')->insert([['share_recipe_id' => 145, 'share_item_id' => 201, 'chance' => 30], ['share_recipe_id' => 145, 'share_item_id' => 202, 'chance' => 70]]);
        $migration = require database_path('migrations/2026_10_19_000003_move_artifact_assemblies_out_of_recipes.php');
        $migration->up();
        $assembly = ItemAssembly::where('legacy_recipe_id', 145)->firstOrFail();
        $this->assertSame([101, 102, 103, 104], $assembly->components->modelKeys());
        $this->assertSame([30, 70], $assembly->rewards->pluck('pivot.weight')->all());
        $this->assertDatabaseMissing('share_recipes', ['id' => 145]);
        $this->assertDatabaseCount('share_items', 6);
        $this->assertDatabaseHas('backpacks', ['item_id' => 104, 'count' => 2]);
        $migration->down();
        $this->assertDatabaseHas('share_recipes', ['id' => 145, 'share_item_id' => 104]);
        $this->assertDatabaseHas('share_recipe_results', ['share_recipe_id' => 145, 'share_item_id' => 202, 'chance' => 70]);
        $this->assertDatabaseCount('item_assemblies', 0);
    }

    public function test_migration_supports_original_recipe_shard_and_preserves_real_recipe_books(): void
    {
        DB::table('share_items')->where('id', 104)->update(['type' => 'recipe', 'name' => '{"ru":"Осколок Безмолвия"}']);
        DB::table('share_recipes')->insert([['id' => 145, 'share_item_id' => 104, 'percent' => 100, 'is_random_result' => true], ['id' => 146, 'share_item_id' => 103, 'percent' => 100, 'is_random_result' => true]]);
        DB::table('share_items')->where('id', 103)->update(['type' => 'recipe', 'name' => '{"ru":"Настоящая книга рецепта"}']);
        foreach ([101, 102] as $id) {
            DB::table('share_recipe_has_items')->insert(['share_recipe_id' => 145, 'share_item_id' => $id, 'count' => 1]);
        }
        foreach ([145, 146] as $id) {
            DB::table('share_recipe_results')->insert(['share_recipe_id' => $id, 'share_item_id' => 201, 'chance' => 100]);
        }
        $migration = require database_path('migrations/2026_10_19_000003_move_artifact_assemblies_out_of_recipes.php');
        $migration->up();
        $assembly = ItemAssembly::where('legacy_recipe_id', 145)->firstOrFail();
        $this->assertSame([101, 102, 104], $assembly->components->modelKeys());
        $this->assertDatabaseHas('share_items', ['id' => 104, 'type' => 'resource']);
        $this->assertDatabaseHas('share_recipes', ['id' => 146]);
        $migration->down();
        $this->assertDatabaseHas('share_items', ['id' => 104, 'type' => 'recipe']);
        $this->assertDatabaseCount('share_recipe_has_items', 2);
    }

    public function test_blacksmith_page_loads_the_new_catalog_with_existing_tooltip_pipeline(): void
    {
        $assembly = $this->createAssembly();
        Schema::create('locations', fn (Blueprint $table) => $table->id());
        $collector = \Mockery::mock(ItemTooltipCollector::class);
        $collector->shouldReceive('collectFrom')->twice()->andReturnSelf();
        $collector->shouldReceive('renderScript')->once()->andReturn('<script>window.itemTooltip={};</script>');
        $this->app->instance(ItemTooltipCollector::class, $collector);
        $page = app(GetKraftPage::class)->execute(auth()->user(), 8);
        $this->assertCount(1, $page->assemblies);
        $this->assertSame($assembly->id, $page->assemblies[0]['id']);
        $this->assertSame([], $page->recipes);
        $this->assertStringContainsString('itemTooltip', $page->itemTooltipScript);
    }
}
