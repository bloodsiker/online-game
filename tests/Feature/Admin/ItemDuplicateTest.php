<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Item\Infrastructure\Persistence\Models\ShareItemLockConfig;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemBuff;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemDebuff;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemEffect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemUseLimit;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
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
            $table->boolean('is_clan_warehouse_allowed')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_use')->default(false);
            $table->boolean('is_lockpick')->default(false);
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
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->timestamps();
        });
        Schema::create('share_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedInteger('duration_seconds');
            $table->string('reapply_policy')->default('refresh');
            $table->timestamps();
        });
        Schema::create('share_item_use_limits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedSmallInteger('max_uses');
            $table->unsignedInteger('period_seconds');
            $table->timestamps();
        });
        Schema::create('share_item_lock_configs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedSmallInteger('lock_required_skill')->default(0);
            $table->unsignedSmallInteger('lock_duration_seconds')->default(12);
            $table->unsignedTinyInteger('minimum_success_chance_percent')->default(5);
            $table->unsignedSmallInteger('experience_reward')->nullable();
            $table->unsignedTinyInteger('trap_chance_penalty_percent')->default(0);
            $table->unsignedBigInteger('trap_effect_id')->nullable();
            $table->unsignedSmallInteger('trap_effect_duration_seconds')->default(60);
            $table->unsignedTinyInteger('trap_damage_percent')->default(0);
            $table->timestamps();
        });
        Schema::create('share_item_instant_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->string('reward_type');
            $table->timestamps();
        });
        Schema::create('share_item_debuffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedInteger('duration_seconds');
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
            $table->string('unlock_type')->default('single_use');
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
        DB::table('effects')->insert(['id' => 1, 'name' => 'Сила медведя', 'slug' => 'bear_strength', 'type' => 'buff']);
        ShareItemBuff::create(['share_item_id' => $item->id, 'effect_id' => 1, 'duration_seconds' => 60, 'reapply_policy' => 'block']);
        ShareItemDebuff::create(['share_item_id' => $item->id, 'effect_id' => 1, 'duration_seconds' => 30]);
        ShareItemRequirement::create(['share_item_id' => $item->id, 'type' => 'level', 'min_value' => 10]);
        ShareItemUseLimit::create(['share_item_id' => $item->id, 'max_uses' => 2, 'period_seconds' => 86400]);
        ShareItemLockConfig::create([
            'share_item_id' => $item->id,
            'lock_required_skill' => 75,
            'lock_duration_seconds' => 18,
            'experience_reward' => 9,
            'minimum_success_chance_percent' => 11,
            'trap_chance_penalty_percent' => 12,
            'trap_damage_percent' => 15,
        ]);

        $recipe = new ShareRecipe;
        $recipe->share_item_id = $item->id;
        $recipe->kraft_item_id = $craftedItem->id;
        $recipe->percent = 83;
        $recipe->unlock_type = 'learnable';
        $recipe->save();
        $recipe->items()->attach($ingredient->id, ['count' => 4]);
        $item->itemHasItems()->attach($containedItem->id, ['min_count' => 2, 'max_count' => 5, 'drop_chance' => 35]);

        (new ItemController)->duplicate($item);

        $copy = ShareItem::where('name', 'Сундук мастера (копия)')->firstOrFail();
        $copy->load(['stats', 'effects', 'buffs', 'debuffs', 'requirements', 'recipe.items', 'itemHasItems', 'useLimit', 'lockConfig']);

        $this->assertNotSame($item->id, $copy->id);
        $this->assertSame($item->description, $copy->description);
        $this->assertSame($item->getRawOriginal('image'), $copy->getRawOriginal('image'));
        $this->assertSame($item->price, $copy->price);
        $this->assertSame(3, $copy->stats->sole()->value);
        $this->assertSame(7, $copy->effects->sole()->value);
        $this->assertSame(60, $copy->buffs->sole()->duration_seconds);
        $this->assertSame(1, $copy->buffs->sole()->effect_id);
        $this->assertSame('block', $copy->buffs->sole()->reapply_policy->value);
        $this->assertSame(2, $copy->useLimit->max_uses);
        $this->assertSame(86400, $copy->useLimit->period_seconds);
        $this->assertSame(75, $copy->lockConfig->lock_required_skill);
        $this->assertSame(9, $copy->lockConfig->experience_reward);
        $this->assertSame(11, $copy->lockConfig->minimum_success_chance_percent);
        $this->assertSame(12, $copy->lockConfig->trap_chance_penalty_percent);
        $this->assertSame(15, $copy->lockConfig->trap_damage_percent);
        $this->assertSame(30, $copy->debuffs->sole()->duration_seconds);
        $this->assertSame(10, $copy->requirements->sole()->min_value);
        $this->assertNotNull($copy->recipe);
        $this->assertSame($craftedItem->id, $copy->recipe->kraft_item_id);
        $this->assertSame(83, $copy->recipe->percent);
        $this->assertTrue($copy->recipe->isLearnable());
        $this->assertSame(4, $copy->recipe->items->sole()->pivot->count);
        $this->assertSame($ingredient->id, $copy->recipe->items->sole()->id);
        $this->assertSame($containedItem->id, $copy->itemHasItems->sole()->id);
        $this->assertSame(2, $copy->itemHasItems->sole()->pivot->min_count);
        $this->assertSame(5, $copy->itemHasItems->sole()->pivot->max_count);
        $this->assertSame(35, $copy->itemHasItems->sole()->pivot->drop_chance);
    }

    public function test_admin_lock_fields_are_saved_to_the_chest_config(): void
    {
        DB::table('effects')->insert([
            'id' => 7,
            'name' => 'Ловушка сундука',
            'slug' => 'chest-trap',
            'type' => 'debuff',
        ]);

        $item = ShareItem::create([
            'name' => 'Запертый сундук',
            'type' => ShareItemType::CHEST,
        ]);
        $request = Request::create('/admin/item/'.$item->id, 'POST', [
            'lock_required_skill' => 85,
            'lock_duration_seconds' => 24,
            'lockpicking_experience_reward' => 13,
            'minimum_success_chance_percent' => 9,
            'trap_chance_penalty_percent' => 17,
            'trap_effect_id' => 7,
            'trap_effect_duration_seconds' => 150,
            'trap_damage_percent' => 11,
        ]);

        $method = new \ReflectionMethod(ItemController::class, 'syncLockConfig');
        $method->invoke(new ItemController, $item, $request);

        $this->assertDatabaseHas('share_item_lock_configs', [
            'share_item_id' => $item->id,
            'lock_required_skill' => 85,
            'lock_duration_seconds' => 24,
            'experience_reward' => 13,
            'minimum_success_chance_percent' => 9,
            'trap_chance_penalty_percent' => 17,
            'trap_effect_id' => 7,
            'trap_effect_duration_seconds' => 150,
            'trap_damage_percent' => 11,
        ]);
    }

    public function test_chest_content_can_be_added_updated_and_deleted(): void
    {
        $chest = ShareItem::create(['name' => 'Сундук', 'type' => ShareItemType::CHEST]);
        $reward = ShareItem::create(['name' => 'Награда', 'type' => ShareItemType::RESOURCE]);
        $controller = new ItemController;

        $controller->addChestContent(Request::create('/', 'POST', [
            'share_item_id' => $reward->id,
            'drop_chance' => 35,
            'min_count' => 2,
            'max_count' => 5,
        ]), $chest);

        $this->assertDatabaseHas('share_item_has_items', [
            'parent_item_id' => $chest->id,
            'share_item_id' => $reward->id,
            'drop_chance' => 35,
            'min_count' => 2,
            'max_count' => 5,
        ]);

        $controller->updateChestContent(Request::create('/', 'PATCH', [
            'drop_chance' => 80,
            'min_count' => 1,
            'max_count' => 3,
        ]), $chest, $reward);

        $this->assertDatabaseHas('share_item_has_items', [
            'parent_item_id' => $chest->id,
            'share_item_id' => $reward->id,
            'drop_chance' => 80,
            'min_count' => 1,
            'max_count' => 3,
        ]);

        $controller->deleteChestContent($chest, $reward);

        $this->assertDatabaseMissing('share_item_has_items', [
            'parent_item_id' => $chest->id,
            'share_item_id' => $reward->id,
        ]);
    }

    public function test_resource_bonus_drop_can_be_configured(): void
    {
        $resource = ShareItem::create(['name' => 'Сосновое бревно', 'type' => ShareItemType::RESOURCE]);
        $resin = ShareItem::create(['name' => 'Смола', 'type' => ShareItemType::RESOURCE]);

        (new ItemController)->addChestContent(Request::create('/', 'POST', [
            'share_item_id' => $resin->id,
            'drop_chance' => 20,
            'min_count' => 1,
            'max_count' => 1,
        ]), $resource);

        $this->assertDatabaseHas('share_item_has_items', [
            'parent_item_id' => $resource->id,
            'share_item_id' => $resin->id,
            'drop_chance' => 20,
            'min_count' => 1,
            'max_count' => 1,
        ]);
    }
}
