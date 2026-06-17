<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeons', function (Blueprint $table) {
            $table->enum('death_behavior', ['exit', 'return_to_start', 'kick_can_reenter'])
                ->default('return_to_start')
                ->after('return_location_id');
            $table->unsignedBigInteger('death_return_location_id')->nullable()->after('death_behavior');

            $table->foreign('death_return_location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('dungeons', function (Blueprint $table) {
            $table->dropForeign(['death_return_location_id']);
            $table->dropColumn(['death_return_location_id', 'death_behavior']);
        });
    }
};
