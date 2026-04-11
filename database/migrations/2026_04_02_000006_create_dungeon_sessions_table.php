<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeon_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('user_id');
            // For party: all members point to the leader's session (shared monster pool).
            // For solo: NULL (use own id).
            $table->unsignedBigInteger('primary_session_id')->nullable();
            $table->timestamp('entered_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            // Момент завершения данжа (все волны пройдены, награды выданы)
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->foreign('dungeon_id')->references('id')->on('dungeons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('primary_session_id')->references('id')->on('dungeon_sessions')->onDelete('cascade');
            $table->unsignedTinyInteger('current_wave')->default(1);
        });

        Schema::table('monster_on_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('dungeon_session_id')->nullable()->after('dungeon_run_floor_id');

            $table->foreign('dungeon_session_id')
                ->references('id')
                ->on('dungeon_sessions')
                ->onDelete('cascade');

            $table->index('dungeon_session_id');
        });

        Schema::table('item_on_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('dungeon_session_id')->nullable()->after('location_id');
            $table->foreign('dungeon_session_id')->references('id')->on('dungeon_sessions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('item_on_locations', function (Blueprint $table) {
            $table->dropForeign(['dungeon_session_id']);
            $table->dropColumn('dungeon_session_id');
        });

        Schema::table('monster_on_locations', function (Blueprint $table) {
            $table->dropForeign(['dungeon_session_id']);
            $table->dropIndex(['dungeon_session_id']);
            $table->dropColumn('dungeon_session_id');
        });

        Schema::dropIfExists('dungeon_sessions');
    }
};
