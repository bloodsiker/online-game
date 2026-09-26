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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('id');
            $table->unsignedBigInteger('prev_location_id')->nullable()->after('location_id');
            $table->dateTime('last_online_at')->nullable()->after('email');

            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('prev_location_id')->references('id')->on('locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['location_id', 'prev_location_id']);
            $table->dropColumn('prev_location_id');
            $table->dropColumn('location_id');
            $table->dropColumn('last_online');
        });
    }
};
