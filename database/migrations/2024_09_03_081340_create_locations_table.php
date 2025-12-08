<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('north')->nullable();
            $table->unsignedBigInteger('south')->nullable();
            $table->unsignedBigInteger('east')->nullable();
            $table->unsignedBigInteger('west')->nullable();
            $table->unsignedBigInteger('up')->nullable();
            $table->unsignedBigInteger('down')->nullable();
            $table->smallInteger('count_monster')->default(0);
            $table->smallInteger('percent_respawn_monster')->default(0);
            $table->smallInteger('time_not_attack')->default(0);
            $table->dateTime('last_respawn_monster_at')->nullable();
            $table->timestamps();

            $table->foreign('map_id')->references('id')->on('maps');
            $table->foreign('north')->references('id')->on('locations');
            $table->foreign('south')->references('id')->on('locations');
            $table->foreign('east')->references('id')->on('locations');
            $table->foreign('west')->references('id')->on('locations');
            $table->foreign('up')->references('id')->on('locations');
            $table->foreign('down')->references('id')->on('locations');
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->foreignId('resp_location_id')->after('slug')->constrained('locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['resp_location_id']);
            $table->dropColumn('resp_location_id');
        });
        Schema::dropIfExists('locations');
    }
};
