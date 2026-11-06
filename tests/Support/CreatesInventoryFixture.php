<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesInventoryFixture
{
    use CreatesEconomyFixture;

    private function createInventoryTables(): void
    {
        $this->createEconomyTables();
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('location_id')->default(1);
            $table->unsignedBigInteger('player_id')->nullable();
        });
        Schema::table('locations', fn (Blueprint $table) => $table->unsignedBigInteger('dungeon_id')->nullable());
        Schema::table('share_items', function (Blueprint $table): void {
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_give')->default(true);
        });
        Schema::table('items', fn (Blueprint $table) => $table->timestamp('expires_at')->nullable());
        Schema::create('players', fn (Blueprint $table) => $table->id());
        Schema::create('item_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('location_id');
            $table->integer('count')->default(1);
            $table->string('interaction_type')->default('pickup');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedBigInteger('world_event_run_id')->nullable();
            $table->unsignedBigInteger('loot_owner_id')->nullable();
            $table->timestamp('loot_reserved_until')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('share_item_instant_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('reward_type');
        });
        foreach (['item_gems' => 'socket_index', 'item_runes' => 'slot_index'] as $table => $index) {
            Schema::create($table, function (Blueprint $table) use ($index): void {
                $table->id();
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('share_item_id');
                $table->integer($index)->default(0);
                $table->timestamps();
            });
        }
    }

    private function seedInventoryFixture(): void
    {
        $this->seedEconomyFixture();
        DB::table('locations')->insert([['id' => 1], ['id' => 2]]);
        DB::table('players')->insert(['id' => 1]);
        DB::table('users')->where('id', 1)->update(['player_id' => 1]);
    }
}
