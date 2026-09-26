<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_events', function (Blueprint $table): void {
            $table->string('objective_type', 20)->default('collect')->after('description')->index();
            $table->unsignedBigInteger('share_item_id')->nullable()->change();
            $table->foreignId('monster_id')
                ->nullable()
                ->after('share_item_id')
                ->constrained('monsters')
                ->restrictOnDelete();
        });

        Schema::table('monster_on_locations', function (Blueprint $table): void {
            $table->foreignId('world_event_run_id')
                ->nullable()
                ->after('location_id')
                ->constrained('world_event_runs')
                ->nullOnDelete();
            $table->index(['world_event_run_id', 'active'], 'event_monsters_active_idx');
        });
    }

    public function down(): void
    {
        DB::table('world_events')->where('objective_type', 'kill')->delete();

        Schema::table('monster_on_locations', function (Blueprint $table): void {
            $table->dropIndex('event_monsters_active_idx');
            $table->dropConstrainedForeignId('world_event_run_id');
        });

        Schema::table('world_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('monster_id');
            $table->dropIndex(['objective_type']);
            $table->dropColumn('objective_type');
            $table->unsignedBigInteger('share_item_id')->nullable(false)->change();
        });
    }
};
