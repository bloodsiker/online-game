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

final class RecipeResultWeightTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('share_recipe_results', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('chance');
        });
        DB::table('share_items')->insert([['id' => 1, 'name' => '{"ru":"Артефакт"}'], ['id' => 2, 'name' => '{"ru":"Другой"}']]);
        DB::table('share_recipes')->insert(['id' => 1]);
        DB::table('share_recipe_results')->insert(['share_recipe_id' => 1, 'share_item_id' => 1, 'chance' => 10]);
    }

    public function test_weight_can_be_updated_without_adding_or_removing_pool_items(): void
    {
        $response = (new ItemController)->updateResultInRecipe(Request::create('/admin/item/recipe/1/result/1', 'POST', ['chance' => 75]), ShareRecipe::findOrFail(1), ShareItem::findOrFail(1));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertDatabaseHas('share_recipe_results', ['share_recipe_id' => 1, 'share_item_id' => 1, 'chance' => 75]);
        $this->assertDatabaseCount('share_recipe_results', 1);
    }

    public function test_weight_update_cannot_add_an_item_not_in_the_pool(): void
    {
        try {
            (new ItemController)->updateResultInRecipe(Request::create('/admin/item/recipe/1/result/2', 'POST', ['chance' => 75]), ShareRecipe::findOrFail(1), ShareItem::findOrFail(2));
            $this->fail('Item outside the pool was accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('share_recipe_results', 1);
    }

    public function test_invalid_weights_are_rejected(): void
    {
        foreach ([0, -1, 1.5, 1000001, 'abc'] as $weight) {
            try {
                (new ItemController)->updateResultInRecipe(Request::create('/admin/item/recipe/1/result/1', 'POST', ['chance' => $weight]), ShareRecipe::findOrFail(1), ShareItem::findOrFail(1));
                $this->fail('Invalid weight was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('chance', $exception->errors());
            }
        }
        $this->assertDatabaseHas('share_recipe_results', ['share_item_id' => 1, 'chance' => 10]);
    }
}
