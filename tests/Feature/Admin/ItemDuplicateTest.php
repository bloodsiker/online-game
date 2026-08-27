<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemEffect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ItemDuplicateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('resource');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('rarity')->default('common');
            $table->string('slot')->nullable();
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->integer('count_use')->default(0);
            $table->integer('expire')->nullable();
            $table->boolean('is_two_hand')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_auction_sellable')->default(false);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_weight')->default(true);
            $table->boolean('is_slot_usable')->default(false);
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('skill_lvl')->nullable();
            $table->integer('skill_exp')->nullable();
            $table->string('upgrade_scroll_type')->nullable();
            $table->json('gem_stats')->nullable();
            $table->string('rune_rarity')->nullable();
            $table->json('rune_stat_pool')->nullable();
            $table->timestamps();
        });
        Schema::create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->integer('value');
            $table->string('value_type')->default('flat');
            $table->timestamps();
        });
        Schema::create('share_item_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('effect_type');
            $table->integer('value');
            $table->string('value_type')->default('flat');
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
        });
        Schema::create('share_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('type');
            $table->string('stat_key')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('min_value');
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->integer('percent')->default(100);
            $table->timestamps();
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count');
            $table->timestamps();
        });
        Schema::create('share_item_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('min_count');
            $table->integer('max_count');
            $table->integer('drop_chance');
            $table->timestamps();
        });
        Schema::create('magic_skill_books', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('magic_skill_id')->unique();
            $table->timestamps();
        });

        app('redirect')->setSession($this->app->make('session.store'));
    }

    public function test_duplicate_copies_the_item_and_its_owned_relations(): void
    {
        $ingredient = ShareItem::create(['name' => 'Ингредиент', 'type' => ShareItemType::RESOURCE]);
        $containedItem = ShareItem::create(['name' => 'Содержимое', 'type' => ShareItemType::RESOURCE]);
        $craftedItem = ShareItem::create(['name' => 'Результат', 'type' => ShareItemType::RESOURCE]);
        $item = ShareItem::create([
            'name' => 'Сундук мастера',
            'type' => ShareItemType::CHEST,
            'description' => 'Тестовая вещь',
            'image' => 'items/test.gif',
            'price' => 55,
        ]);

        ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => 'attack_min', 'value' => 3, 'value_type' => 'flat']);
        ShareItemEffect::create(['share_item_id' => $item->id, 'effect_type' => 'heal_hp', 'value' => 7, 'value_type' => 'percent', 'duration_seconds' => 5]);
        ShareItemRequirement::create(['share_item_id' => $item->id, 'type' => 'level', 'min_value' => 10]);

        $recipe = new ShareRecipe;
        $recipe->share_item_id = $item->id;
        $recipe->kraft_item_id = $craftedItem->id;
        $recipe->percent = 83;
        $recipe->save();
        $recipe->items()->attach($ingredient->id, ['count' => 4]);
        $item->itemHasItems()->attach($containedItem->id, ['min_count' => 2, 'max_count' => 5, 'drop_chance' => 35]);

        (new ItemController)->duplicate($item);

        $copy = ShareItem::where('name', 'Сундук мастера (копия)')->firstOrFail();
        $copy->load(['stats', 'effects', 'requirements', 'recipe.items', 'itemHasItems']);

        $this->assertNotSame($item->id, $copy->id);
        $this->assertSame($item->description, $copy->description);
        $this->assertSame($item->getRawOriginal('image'), $copy->getRawOriginal('image'));
        $this->assertSame($item->price, $copy->price);
        $this->assertSame(3, $copy->stats->sole()->value);
        $this->assertSame(7, $copy->effects->sole()->value);
        $this->assertSame(10, $copy->requirements->sole()->min_value);
        $this->assertNotNull($copy->recipe);
        $this->assertSame($craftedItem->id, $copy->recipe->kraft_item_id);
        $this->assertSame(83, $copy->recipe->percent);
        $this->assertSame(4, $copy->recipe->items->sole()->pivot->count);
        $this->assertSame($ingredient->id, $copy->recipe->items->sole()->id);
        $this->assertSame($containedItem->id, $copy->itemHasItems->sole()->id);
        $this->assertSame(2, $copy->itemHasItems->sole()->pivot->min_count);
        $this->assertSame(5, $copy->itemHasItems->sole()->pivot->max_count);
        $this->assertSame(35, $copy->itemHasItems->sole()->pivot->drop_chance);
    }
}
