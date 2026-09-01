<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key', 'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key'])->default('resource');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('transparent_image')->nullable();
            $table->integer('count_use')->default(0);
            $table->unsignedTinyInteger('max_drop_level_difference')->nullable()->comment('Maximum player level above monster level for monster drops; null means no restriction');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true)->comment('Можно ли игроку выбросить предмет из рюкзака');
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->enum('upgrade_scroll_type', ['base', 'protection', 'stabilizer', 'lucky'])->nullable();
            $table->json('gem_stats')->nullable(); // Параметры камня на share_items (заполняется только для type=gem)
            $table->enum('slot', ['hand', 'helmet', 'shoulder', 'forearm', 'armor', 'legging', 'chain_armor', 'cloak', 'shoes', 'gloves', 'belt', 'bag'])->nullable();
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->timestamps();
        });

        Schema::create('share_item_has_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->integer('min_count')->default(1);
            $table->integer('max_count')->default(1);
            $table->integer('drop_chance')->default(5);
            $table->timestamps();
        });

        Schema::create('share_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->foreignId('kraft_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->integer('percent')->default(60);
            $table->timestamps();
        });

        Schema::create('share_recipe_has_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_recipe_id')->constrained('share_recipes')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->integer('count');
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->integer('upgrade_lvl')->default(0);
            $table->unsignedSmallInteger('upgrade_pity')->default(0);
            $table->unsignedSmallInteger('upgrade_fail_streak')->default(0);
            $table->integer('additional_attack')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_open')->default(false);
            $table->unsignedTinyInteger('socket_count')->default(0);  // Сокеты на конкретном предмете (0–4)
            $table->timestamps();
        });

        // Вставленные камни в предмет
        Schema::create('item_gems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedTinyInteger('socket_index'); // 0, 1, 2
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['item_id', 'socket_index']);
        });

        Schema::create('item_on_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->timestamps();
        });

        Schema::create('item_in_chest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chest_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_in_chest');
        Schema::dropIfExists('item_on_locations');
        Schema::dropIfExists('item_gems');
        Schema::dropIfExists('items');
        Schema::dropIfExists('share_recipe_has_items');
        Schema::dropIfExists('share_recipes');
        Schema::dropIfExists('share_item_has_items');
        Schema::dropIfExists('share_items');
        Schema::dropIfExists('share_item_types');
    }
};
