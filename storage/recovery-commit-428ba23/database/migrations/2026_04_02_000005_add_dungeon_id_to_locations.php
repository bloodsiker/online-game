<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('dungeon_id')->nullable()->after('map_id');
            $table->foreign('dungeon_id')->references('id')->on('dungeons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['dungeon_id']);
            $table->dropColumn('dungeon_id');
        });
    }
};