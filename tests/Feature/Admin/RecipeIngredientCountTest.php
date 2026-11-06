<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RecipeIngredientCountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
        });
        Schema::create('share_recipes', fn (Blueprint $table) => $table->id());
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count');
        });
        DB::table('share_items')->insert([['id' => 1, 'name' => '{"ru":"Металл"}'], ['id' => 2, 'name' => '{"ru":"Дерево"}']]);
        DB::table('share_recipes')->insert([['id' => 1], ['id' => 2]]);
        DB::table('share_recipe_has_items')->insert([
            ['share_recipe_id' => 1, 'share_item_id' => 1, 'count' => 3],
            ['share_recipe_id' => 2, 'share_item_id' => 1, 'count' => 4],
        ]);
    }

    private function update(mixed $count, int $itemId = 1): void
    {
        (new ItemController)->updateIngredientInRecipe(
            request: Request::create('/admin/item/recipe/1/ingredient/'.$itemId, 'POST', ['count' => $count]),
            recipe: ShareRecipe::findOrFail(1), item: ShareItem::findOrFail($itemId),
        );
    }

    public function test_quantity_changes_only_in_the_selected_recipe(): void
    {
        $this->update(7);
        $this->assertDatabaseHas('share_recipe_has_items', ['share_recipe_id' => 1, 'share_item_id' => 1, 'count' => 7]);
        $this->assertDatabaseHas('share_recipe_has_items', ['share_recipe_id' => 2, 'share_item_id' => 1, 'count' => 4]);
        $this->assertDatabaseCount('share_recipe_has_items', 2);
    }

    public function test_quantity_update_does_not_add_an_unrelated_ingredient(): void
    {
        try {
            $this->update(7, itemId: 2);
            $this->fail('Unrelated ingredient accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('share_recipe_has_items', 2);
    }

    public function test_invalid_quantities_do_not_change_the_recipe(): void
    {
        foreach ([0, -1, 1.5, 2147483648, null, 'abc'] as $count) {
            try {
                $this->update($count);
                $this->fail('Invalid quantity accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('count', $exception->errors());
            }
        }
        $this->assertDatabaseHas('share_recipe_has_items', ['share_recipe_id' => 1, 'share_item_id' => 1, 'count' => 3]);
    }
}
