<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SkillController;
use App\Modules\Share\Application\UseCases\Admin\ManageProfessionRecipes;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfessionRecipeOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('skills', fn (Blueprint $table) => $table->id());
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->string('name');
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->integer('percent')->default(100);
        });
        DB::table('skills')->insert([['id' => 19], ['id' => 20]]);
        DB::table('share_items')->insert([
            ['id' => 101, 'skill_id' => 19, 'name' => 'Первый рецепт'],
            ['id' => 102, 'skill_id' => 19, 'name' => 'Второй рецепт'],
            ['id' => 103, 'skill_id' => 20, 'name' => 'Другая профессия'],
        ]);
        DB::table('share_recipes')->insert([['id' => 1, 'share_item_id' => 101], ['id' => 2, 'share_item_id' => 102], ['id' => 3, 'share_item_id' => 103]]);
        (require database_path('migrations/2026_11_05_000001_add_sort_order_to_recipes.php'))->up();
    }

    public function test_profession_order_does_not_change_other_professions_or_recipe_settings(): void
    {
        app(ManageProfessionRecipes::class)->reorder(Skill::findOrFail(19), collect([2, 1]));
        $this->assertDatabaseHas('share_recipes', ['id' => 2, 'sort_order' => 1, 'percent' => 100]);
        $this->assertDatabaseHas('share_recipes', ['id' => 1, 'sort_order' => 2, 'percent' => 100]);
        $this->assertDatabaseHas('share_recipes', ['id' => 3, 'sort_order' => 0]);
    }

    public function test_incomplete_duplicate_foreign_and_stale_lists_are_rejected(): void
    {
        foreach ([[1], [1, 1], [1, 3], [1, 2, 3]] as $ids) {
            try {
                app(ManageProfessionRecipes::class)->reorder(Skill::findOrFail(19), collect($ids));
                $this->fail('Invalid list accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('recipe_ids', $exception->errors());
            }
        }
        $this->assertSame(0, (int) DB::table('share_recipes')->sum('sort_order'));
    }

    public function test_migration_keeps_all_existing_recipes(): void
    {
        (require database_path('migrations/2026_11_05_000001_add_sort_order_to_recipes.php'))->up();
        $this->assertDatabaseCount('share_recipes', 3);
        $this->assertSame([1, 2, 3], DB::table('share_recipes')->orderBy('sort_order')->orderBy('id')->pluck('id')->all());
    }

    public function test_profession_list_uses_saved_order_and_appends_new_recipes(): void
    {
        $catalog = app(ManageProfessionRecipes::class);
        $skill = Skill::findOrFail(19);
        $this->assertSame([1, 2], $catalog->get($skill)->pluck('id')->all());
        $catalog->reorder($skill, collect([2, 1]));
        DB::table('share_items')->insert(['id' => 104, 'skill_id' => 19, 'name' => 'Новый рецепт']);
        DB::table('share_recipes')->insert(['id' => 4, 'share_item_id' => 104]);
        $this->assertSame([2, 1, 4], $catalog->get($skill)->pluck('id')->all());
        $this->assertSame([3], $catalog->get(Skill::findOrFail(20))->pluck('id')->all());
    }

    public function test_admin_form_saves_string_ids_and_redirects_to_profession_list(): void
    {
        $response = (new SkillController)->saveRecipeOrder(
            request: Request::create('/admin/skill/19/recipes/order', 'POST', ['recipe_ids' => ['2', '1']]),
            skill: Skill::findOrFail(19), catalog: app(ManageProfessionRecipes::class),
        );
        $this->assertSame(route('admin.skill.recipes', 19), $response->getTargetUrl());
        $this->assertSame([2, 1], app(ManageProfessionRecipes::class)->get(Skill::findOrFail(19))->pluck('id')->all());
    }
}
