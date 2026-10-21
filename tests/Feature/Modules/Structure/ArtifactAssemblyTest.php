<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ArtifactAssemblyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->withoutMiddleware();
        $this->actingAs((new User)->forceFill(['id' => 1])->setRelation('player', null));
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type');
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            foreach (array_keys((new Item)->getAttributes()) as $attribute) {
                $table->integer($attribute)->default(0);
            }
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
            $table->integer('percent');
            $table->boolean('is_random_result');
            $table->string('unlock_type');
        });
        foreach (['share_recipe_has_items' => 'count', 'share_recipe_results' => 'chance'] as $name => $quantity) {
            Schema::create($name, function (Blueprint $table) use ($quantity): void {
                $table->id();
                $table->unsignedBigInteger('share_recipe_id');
                $table->unsignedBigInteger('share_item_id');
                $table->integer($quantity);
            });
        }
        foreach ([101, 102, 103, 104, 201] as $id) {
            DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Предмет '.$id]), 'type' => $id === 104 ? 'recipe' : ($id === 201 ? 'artifact' : 'resource')]);
            if ($id === 201) {
                continue;
            }
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $id]);
            DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => $id, 'count' => 2]);
        }
        DB::table('share_recipes')->insert(['id' => 1, 'share_item_id' => 104, 'percent' => 100, 'is_random_result' => true, 'unlock_type' => 'single_use']);
        foreach ([101, 102, 103] as $id) {
            DB::table('share_recipe_has_items')->insert(['share_recipe_id' => 1, 'share_item_id' => $id, 'count' => 1]);
        }
        DB::table('share_recipe_results')->insert(['share_recipe_id' => 1, 'share_item_id' => 201, 'chance' => 100]);
    }

    public function test_ajax_returns_actual_reward_and_consumes_one_of_each_part(): void
    {
        $this->postJson(route('blacksmith.kraft', 104))
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('success', true)
            ->assertJsonPath('crafted_share_item_id', 201);
        foreach ([101, 102, 103, 104] as $id) {
            $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'item_id' => $id, 'count' => 1]);
        }
        $this->assertSame(1, Item::where('share_item_id', 201)->count());

        $this->postJson(route('blacksmith.kraft', 104))->assertOk();
        $this->postJson(route('blacksmith.kraft', 104))->assertNotFound();
        $this->assertSame(2, Item::where('share_item_id', 201)->count());
    }

    public function test_missing_part_does_not_grant_reward_or_consume_other_parts(): void
    {
        DB::table('backpacks')->where('item_id', 102)->update(['count' => 0]);
        $this->postJson(route('blacksmith.kraft', 104))->assertUnprocessable()->assertJsonPath('ok', false)->assertJsonPath('crafted_share_item_id', null);
        $this->assertDatabaseHas('backpacks', ['item_id' => 104, 'count' => 2]);
        $this->assertDatabaseHas('backpacks', ['item_id' => 101, 'count' => 2]);
        $this->assertSame(0, Item::where('share_item_id', 201)->count());
    }

    public function test_other_players_parts_cannot_be_assembled(): void
    {
        DB::table('backpacks')->where('item_id', 104)->update(['user_id' => 2]);
        $this->postJson(route('blacksmith.kraft', 104))->assertNotFound();
        $this->assertDatabaseHas('backpacks', ['user_id' => 2, 'item_id' => 104, 'count' => 2]);
    }
}
