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
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['shop', 'auction', 'heal', 'warehouse', 'bank', 'blacksmith'])->default('shop');
            $table->string('name');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('share_actions', function (Blueprint $table) {
            $table->id();
            $table->string('alias');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('structure_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_action_id')->nullable()->constrained('share_actions')->nullOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('location_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_action_id')->nullable()->constrained('share_actions')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->integer('count')->default(1);
            $table->timestamps();
        });

        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->integer('price')->default(0);
            $table->boolean('is_anonymous')->default(1);
            $table->timestamps();
        });

        Schema::create('auction_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sell_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->integer('price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('structure_actions');
        Schema::dropIfExists('structure_actions');
        Schema::dropIfExists('share_actions');
        Schema::dropIfExists('shop_items');
        Schema::dropIfExists('structures');
    }
};
