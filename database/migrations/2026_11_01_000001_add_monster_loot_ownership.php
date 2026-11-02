<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battle_monster_damage', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('battle_id')->constrained('battles')->cascadeOnDelete();
            $table->foreignId('location_monster_id')->constrained('monster_on_locations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('damage');
            $table->index(['battle_id', 'location_monster_id', 'user_id'], 'battle_monster_damage_recipient');
        });
        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->foreignId('loot_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('loot_reserved_until')->nullable();
        });
        Schema::table('monster_active_effects', function (Blueprint $table): void {
            $table->foreignId('source_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('monster_active_effects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('source_user_id');
        });
        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('loot_owner_id');
            $table->dropColumn('loot_reserved_until');
        });
        Schema::dropIfExists('battle_monster_damage');
    }
};
