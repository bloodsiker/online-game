<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Share\Application\UseCases\Admin\ReorderRecipeIngredients;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecipeIngredientOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('share_recipes', fn (Blueprint $table) => $table->id());
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count');
        });
        DB::table('share_recipes')->insert([['id' => 1], ['id' => 2]]);
        DB::table('share_items')->insert([['id' => 1, 'name' => '{"ru":"Металл"}'], ['id' => 2, 'name' => '{"ru":"Дерево"}']]);
        DB::table('share_recipe_has_items')->insert([
            ['id' => 11, 'share_recipe_id' => 1, 'share_item_id' => 1, 'count' => 3],
            ['id' => 12, 'share_recipe_id' => 1, 'share_item_id' => 2, 'count' => 5],
            ['id' => 22, 'share_recipe_id' => 2, 'share_item_id' => 1, 'count' => 7],
        ]);
        (require database_path('migrations/2026_11_04_000002_add_sort_order_to_recipe_ingredients.php'))->up();
    }

    public function test_saving_order_preserves_quantities_and_other_recipes(): void
    {
        (new ItemController)->updateIngredientOrder(
            request: Request::create('/admin/item/recipe/1/ingredient-order', 'POST', ['ingredient_ids' => ['12', '11']]),
            recipe: ShareRecipe::findOrFail(1), reorder: app(ReorderRecipeIngredients::class),
        );
        $this->assertSame([12, 11], DB::table('share_recipe_has_items')->where('share_recipe_id', 1)->orderBy('sort_order')->pluck('id')->all());
        $this->assertDatabaseHas('share_recipe_has_items', ['id' => 11, 'count' => 3]);
        $this->assertDatabaseHas('share_recipe_has_items', ['id' => 12, 'count' => 5]);
        $this->assertDatabaseHas('share_recipe_has_items', ['id' => 22, 'count' => 7, 'sort_order' => 0]);
        $this->assertSame([2, 1], ShareRecipe::findOrFail(1)->items->pluck('id')->all());
    }

    public function test_new_ingredients_are_appended_to_the_saved_order(): void
    {
        app(ReorderRecipeIngredients::class)->execute(ShareRecipe::findOrFail(1), collect([12, 11]));
        DB::table('share_items')->insert(['id' => 3, 'name' => '{"ru":"Кожа"}']);
        (new ItemController)->addItemToRecipe(
            request: Request::create('/admin/item/recipe/1', 'POST', ['share_item_id' => 3, 'count' => 2]),
            recipe: ShareRecipe::findOrFail(1),
        );
        $this->assertSame([2, 1, 3], ShareRecipe::findOrFail(1)->items->pluck('id')->all());
        $this->assertDatabaseHas('share_recipe_has_items', ['share_recipe_id' => 1, 'share_item_id' => 3, 'sort_order' => 3, 'count' => 2]);
    }

    public function test_stale_foreign_duplicate_and_incomplete_orders_do_not_write(): void
    {
        foreach ([[11], [11, 11], [11, 22], [11, 12, 22], []] as $ids) {
            try {
                app(ReorderRecipeIngredients::class)->execute(ShareRecipe::findOrFail(1), collect($ids));
                $this->fail('Invalid order accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('ingredient_ids', $exception->errors());
            }
        }
        $this->assertSame(0, (int) DB::table('share_recipe_has_items')->sum('sort_order'));
    }

    public function test_migration_preserves_existing_rows_and_is_repeatable(): void
    {
        (require database_path('migrations/2026_11_04_000002_add_sort_order_to_recipe_ingredients.php'))->up();
        $this->assertDatabaseCount('share_recipe_has_items', 3);
        $this->assertSame([11, 12], DB::table('share_recipe_has_items')->where('share_recipe_id', 1)->orderBy('sort_order')->orderBy('id')->pluck('id')->all());
    }
}
