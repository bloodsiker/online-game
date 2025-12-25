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
            $table->enum('type', ['resource', 'weapon', 'shield', 'armor', 'heal', 'key', 'quest', 'artifact', 'recipe', 'chest', 'scroll'])->default('resource');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('min_attack')->default(0);
            $table->integer('max_attack')->default(0);
            $table->integer('armor')->default(0);
            $table->integer('count_use')->default(0);
            $table->tinyInteger('is_heal')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_sell')->default(1);
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->enum('slot', ['hand', 'helmet', 'shoulder', 'forearm', 'armor', 'legging', 'chain_armor', 'cloak', 'shoes', 'gloves'])->nullable();
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
            $table->integer('additional_attack')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_open')->default(0);
            $table->timestamps();
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
        Schema::dropIfExists('items');
        Schema::dropIfExists('share_recipe_has_items');
        Schema::dropIfExists('share_recipes');
        Schema::dropIfExists('share_item_has_items');
        Schema::dropIfExists('share_items');
        Schema::dropIfExists('share_item_types');
    }
};
