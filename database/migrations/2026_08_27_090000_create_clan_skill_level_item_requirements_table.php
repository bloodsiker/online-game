<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clan_skill_level_item_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clan_skill_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();

            $table->unique(['clan_skill_level_id', 'share_item_id'], 'clan_skill_level_item_unique');
        });

        DB::table('clan_skill_levels')
            ->whereNotNull('share_item_id')
            ->orderBy('id')
            ->eachById(function (object $level): void {
                DB::table('clan_skill_level_item_requirements')->insert([
                    'clan_skill_level_id' => $level->id,
                    'share_item_id' => $level->share_item_id,
                    'count' => $level->share_item_count ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_skill_level_item_requirements');
    }
};
