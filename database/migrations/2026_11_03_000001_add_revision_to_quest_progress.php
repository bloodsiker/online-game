<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['quest_players', 'quest_clan_progress'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->unsignedInteger('revision')->default(0));
        }
    }

    public function down(): void
    {
        foreach (['quest_players', 'quest_clan_progress'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('revision'));
        }
    }
};
