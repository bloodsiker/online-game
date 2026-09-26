<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->restrictOnDelete();
            $table->string('influence_name')->nullable()->comment('Название территории в предложном падеже, например: Дартронге');
            $table->timestamp('next_start_at')->nullable()->index();
            $table->unsignedInteger('repeat_interval_minutes')->nullable();
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->unsignedInteger('global_limit');
            $table->unsignedInteger('player_limit');
            $table->unsignedInteger('spawn_limit')->default(10);
            $table->unsignedInteger('respawn_seconds')->default(60);
            $table->unsignedInteger('item_lifetime_minutes')->default(1440);
            $table->unsignedInteger('influence_per_item')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('world_event_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('world_event_id')->constrained('world_events')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->unique(['world_event_id', 'location_id']);
        });

        Schema::create('world_event_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('world_event_id')->constrained('world_events')->cascadeOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->unsignedInteger('collected_count')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->index();
            $table->timestamp('next_spawn_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('world_event_player_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('world_event_run_id')->constrained('world_event_runs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('collected_count')->default(0);
            $table->unsignedBigInteger('influence_earned')->default(0);
            $table->timestamps();
            $table->unique(['world_event_run_id', 'user_id'], 'world_event_player_unique');
        });

        Schema::create('map_influences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('influence')->default(0);
            $table->timestamps();
            $table->unique(['map_id', 'user_id']);
        });

        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->foreignId('world_event_run_id')
                ->nullable()
                ->after('location_id')
                ->constrained('world_event_runs')
                ->nullOnDelete();
            $table->index(['world_event_run_id', 'location_id'], 'event_items_location_idx');
        });

        Schema::table('items', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('is_open')->index();
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });

        Schema::table('item_on_locations', function (Blueprint $table): void {
            $table->dropIndex('event_items_location_idx');
            $table->dropConstrainedForeignId('world_event_run_id');
        });

        Schema::dropIfExists('map_influences');
        Schema::dropIfExists('world_event_player_progress');
        Schema::dropIfExists('world_event_runs');
        Schema::dropIfExists('world_event_locations');
        Schema::dropIfExists('world_events');
    }
};
