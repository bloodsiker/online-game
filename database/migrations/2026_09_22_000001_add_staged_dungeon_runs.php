<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dungeons MODIFY type ENUM('linear','survival','boss_rush','tower') NOT NULL DEFAULT 'linear'");
        }

        Schema::create('dungeon_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dungeon_id')->constrained('dungeons')->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('name', 150);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->string('completion_type', 30)->default('kill_all');
            $table->string('spawn_type', 30)->default('distributed');
            $table->unsignedSmallInteger('total_monsters')->default(0);
            $table->foreignId('next_stage_id')->nullable()->constrained('dungeon_stages')->nullOnDelete();
            $table->timestamps();

            $table->unique(['dungeon_id', 'number']);
        });

        Schema::create('dungeon_stage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dungeon_stage_id')->constrained('dungeon_stages')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->boolean('is_entry')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['dungeon_stage_id', 'location_id']);
        });

        Schema::create('dungeon_stage_monsters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dungeon_stage_id')->constrained('dungeon_stages')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('monster_id')->constrained('monsters')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->timestamps();
        });

        Schema::create('dungeon_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dungeon_id')->constrained('dungeons')->cascadeOnDelete();
            $table->foreignId('leader_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('dungeon_stages')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('stage_started_at')->nullable();
            $table->timestamp('stage_expires_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'stage_expires_at']);
        });

        Schema::table('dungeon_sessions', function (Blueprint $table) {
            $table->foreignId('dungeon_run_id')->nullable()->after('dungeon_id')->constrained('dungeon_runs')->nullOnDelete();
            $table->index(['dungeon_run_id', 'user_id']);
        });

        Schema::create('dungeon_run_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dungeon_run_id')->constrained('dungeon_runs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dungeon_session_id')->nullable()->constrained('dungeon_sessions')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['dungeon_run_id', 'user_id']);
        });

        Schema::table('battles', function (Blueprint $table) {
            $table->foreignId('dungeon_run_id')->nullable()->after('location_id')->constrained('dungeon_runs')->nullOnDelete();
            $table->index(['location_id', 'dungeon_run_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dungeon_run_id');
        });
        Schema::dropIfExists('dungeon_run_participants');
        Schema::table('dungeon_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dungeon_run_id');
        });
        Schema::dropIfExists('dungeon_runs');
        Schema::dropIfExists('dungeon_stage_monsters');
        Schema::dropIfExists('dungeon_stage_locations');
        Schema::dropIfExists('dungeon_stages');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dungeons MODIFY type ENUM('linear','survival','boss_rush') NOT NULL DEFAULT 'linear'");
        }
    }
};
