<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boss_defeat_logs', function (Blueprint $table): void {
            $table->index(['monster_id', 'player_id'], 'boss_defeat_logs_monster_player_index');
        });
    }

    public function down(): void
    {
        Schema::table('boss_defeat_logs', function (Blueprint $table): void {
            $table->dropIndex('boss_defeat_logs_monster_player_index');
        });
    }
};
