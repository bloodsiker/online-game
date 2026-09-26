<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_event_stage_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('world_event_stage_id')->constrained('world_event_stages')->cascadeOnDelete();
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->restrictOnDelete();
            $table->foreignId('monster_id')->nullable()->constrained('monsters')->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('spawn_weight')->default(100);
            $table->unsignedInteger('max_active')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['world_event_stage_id', 'position'], 'world_event_stage_target_position_unique');
            $table->unique(['world_event_stage_id', 'share_item_id'], 'world_event_stage_target_item_unique');
            $table->unique(['world_event_stage_id', 'monster_id'], 'world_event_stage_target_monster_unique');
        });

        $now = now();
        DB::table('world_event_stages')->orderBy('id')->get()->each(function (object $stage) use ($now): void {
            if ($stage->share_item_id === null && $stage->monster_id === null) {
                return;
            }

            DB::table('world_event_stage_targets')->insert([
                'world_event_stage_id' => $stage->id,
                'share_item_id' => $stage->share_item_id,
                'monster_id' => $stage->monster_id,
                'position' => 1,
                'spawn_weight' => 100,
                'max_active' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_event_stage_targets');
    }
};
