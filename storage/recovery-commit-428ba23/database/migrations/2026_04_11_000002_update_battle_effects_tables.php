<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Обновление таблиц активных эффектов:
 * - monster_active_effects: monster_id → location_monster_id (MonsterOnLocation), добавить battle_id, type
 * - player_active_effects: добавить battle_id, type, сделать effect_id nullable (для кастомных эффектов)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── monster_active_effects ────────────────────────────────────────────
        Schema::table('monster_active_effects', function (Blueprint $table) {
            $table->dropForeign(['monster_id']);
            $table->dropUnique(['monster_id', 'effect_id']);
            $table->dropColumn('monster_id');

            $table->unsignedBigInteger('location_monster_id')->after('id');
            $table->foreign('location_monster_id')
                ->references('id')->on('monster_on_locations')->onDelete('cascade');

            $table->string('type')->nullable()->after('location_monster_id');

            $table->unsignedBigInteger('battle_id')->nullable()->after('type');
            $table->foreign('battle_id')
                ->references('id')->on('battles')->onDelete('cascade');

            // effect_id теперь nullable (кастомные эффекты без записи в effects)
            $table->unsignedBigInteger('effect_id')->nullable()->change();
        });

        // ── player_active_effects ─────────────────────────────────────────────
        Schema::table('player_active_effects', function (Blueprint $table) {
            $table->string('type')->nullable()->after('player_id');

            $table->unsignedBigInteger('battle_id')->nullable()->after('type');
            $table->foreign('battle_id')
                ->references('id')->on('battles')->onDelete('cascade');

            $table->unsignedBigInteger('effect_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('monster_active_effects', function (Blueprint $table) {
            $table->dropForeign(['location_monster_id']);
            $table->dropForeign(['battle_id']);
            $table->dropColumn(['location_monster_id', 'type', 'battle_id']);

            $table->unsignedBigInteger('monster_id')->after('id');
            $table->foreign('monster_id')->references('id')->on('monsters')->onDelete('cascade');
            $table->unique(['monster_id', 'effect_id']);
        });

        Schema::table('player_active_effects', function (Blueprint $table) {
            $table->dropForeign(['battle_id']);
            $table->dropColumn(['type', 'battle_id']);
        });
    }
};