<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('npc_id')->unique()->constrained('npcs')->restrictOnDelete();
            $table->string('name');
            $table->unsignedInteger('license_price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('trade_goods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trade_post_id')->constrained()->restrictOnDelete();
            $table->foreignId('share_item_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('base_price');
            $table->unsignedSmallInteger('required_skill_lvl')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('trade_post_stocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trade_good_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_quantity')->default(30);
            $table->unsignedInteger('max_quantity')->default(30);
            $table->unsignedInteger('restock_amount')->default(5);
            $table->unsignedInteger('restock_interval_minutes')->default(30);
            $table->timestamp('last_restock_at');
            $table->timestamps();
        });
        Schema::create('trade_routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trade_post_a_id')->constrained('trade_posts')->restrictOnDelete();
            $table->foreignId('trade_post_b_id')->constrained('trade_posts')->restrictOnDelete();
            $table->string('distance_tier', 8);
            $table->unsignedInteger('exp_per_unit');
            $table->boolean('is_active')->default(true);
            $table->unique(['trade_post_a_id', 'trade_post_b_id']);
            $table->timestamps();
        });
        Schema::create('trade_buy_offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trade_post_id')->constrained()->restrictOnDelete();
            $table->foreignId('trade_good_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('buy_price');
            $table->boolean('is_active')->default(true);
            $table->unique(['trade_post_id', 'trade_good_id']);
            $table->timestamps();
        });
        Schema::create('player_trade_licenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trade_post_id')->constrained()->restrictOnDelete();
            $table->timestamp('purchased_at');
            $table->unique(['player_id', 'trade_post_id']);
        });
        Schema::create('trade_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->restrictOnDelete();
            $table->foreignId('trade_post_id')->constrained()->restrictOnDelete();
            $table->foreignId('trade_good_id')->nullable()->constrained()->restrictOnDelete();
            $table->uuid('request_key');
            $table->string('action', 16);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('experience')->default(0);
            $table->unsignedSmallInteger('skill_level');
            $table->text('message');
            $table->timestamps();
            $table->unique(['player_id', 'request_key']);
            $table->index(['trade_post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['trade_logs', 'player_trade_licenses', 'trade_buy_offers', 'trade_routes',
            'trade_post_stocks', 'trade_goods', 'trade_posts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
