<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_event_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('world_event_id')->constrained('world_events')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('title', 150);
            $table->string('objective_type', 20)->index();
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->restrictOnDelete();
            $table->foreignId('monster_id')->nullable()->constrained('monsters')->restrictOnDelete();
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->unsignedInteger('spawn_limit')->default(10);
            $table->unsignedInteger('respawn_seconds')->default(60);
            $table->unsignedInteger('item_lifetime_minutes')->default(1440);
            $table->unsignedInteger('influence_per_item')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['world_event_id', 'position']);
        });

        Schema::table('world_event_runs', function (Blueprint $table): void {
            $table->foreignId('current_stage_id')
                ->nullable()
                ->after('world_event_id')
                ->constrained('world_event_stages')
                ->nullOnDelete();
        });

        Schema::table('world_event_player_progress', function (Blueprint $table): void {
            $table->foreignId('world_event_stage_id')
                ->nullable()
                ->after('world_event_run_id')
                ->constrained('world_event_stages')
                ->cascadeOnDelete();
        });
        Schema::table('world_event_player_progress', function (Blueprint $table): void {
            $table->unique(
                ['world_event_run_id', 'world_event_stage_id', 'user_id'],
                'world_event_stage_player_unique',
            );
        });
        Schema::table('world_event_player_progress', function (Blueprint $table): void {
            $table->dropUnique('world_event_player_unique');
        });

        $now = now();
        DB::table('world_events')->orderBy('id')->get()->each(function (object $event) use ($now): void {
            $stageId = DB::table('world_event_stages')->insertGetId([
                'world_event_id' => $event->id,
                'position' => 1,
                'title' => 'Этап 1',
                'objective_type' => $event->objective_type,
                'share_item_id' => $event->share_item_id,
                'monster_id' => $event->monster_id,
                'global_limit' => $event->global_limit,
                'player_limit' => $event->player_limit,
                'spawn_limit' => $event->spawn_limit,
                'respawn_seconds' => $event->respawn_seconds,
                'item_lifetime_minutes' => $event->item_lifetime_minutes,
                'influence_per_item' => $event->influence_per_item,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('world_event_runs')
                ->where('world_event_id', $event->id)
                ->update(['current_stage_id' => $stageId]);
            DB::table('world_event_player_progress')
                ->whereIn('world_event_run_id', DB::table('world_event_runs')->where('world_event_id', $event->id)->select('id'))
                ->update(['world_event_stage_id' => $stageId]);
        });
    }

    public function down(): void
    {
        DB::table('world_event_player_progress')
            ->selectRaw('world_event_run_id, user_id, MIN(id) AS keeper_id, SUM(collected_count) AS collected_count, SUM(influence_earned) AS influence_earned')
            ->groupBy('world_event_run_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $progress): void {
                DB::table('world_event_player_progress')
                    ->where('id', $progress->keeper_id)
                    ->update([
                        'collected_count' => $progress->collected_count,
                        'influence_earned' => $progress->influence_earned,
                    ]);
                DB::table('world_event_player_progress')
                    ->where('world_event_run_id', $progress->world_event_run_id)
                    ->where('user_id', $progress->user_id)
                    ->where('id', '!=', $progress->keeper_id)
                    ->delete();
            });

        Schema::table('world_event_player_progress', function (Blueprint $table): void {
            $table->unique(['world_event_run_id', 'user_id'], 'world_event_player_unique');
        });
        Schema::table('world_event_player_progress', function (Blueprint $table): void {
            $table->dropUnique('world_event_stage_player_unique');
        });
        Schema::table('world_event_player_progress', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('world_event_stage_id');
        });
        Schema::table('world_event_runs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('current_stage_id');
        });
        Schema::dropIfExists('world_event_stages');
    }
};
