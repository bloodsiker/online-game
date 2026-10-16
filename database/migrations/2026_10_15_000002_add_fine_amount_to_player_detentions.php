<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('player_detentions') && ! Schema::hasColumn('player_detentions', 'fine_money')) {
            Schema::table('player_detentions', function (Blueprint $table): void {
                $table->unsignedBigInteger('fine_money')->default(0)->after('reason');
            });
        }

        if (Schema::hasTable('player_detentions') && ! Schema::hasColumn('player_detentions', 'fine_diamond')) {
            Schema::table('player_detentions', function (Blueprint $table): void {
                $table->decimal('fine_diamond', 12, 2)->default(0)->after('fine_money');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('player_detentions') && Schema::hasColumn('player_detentions', 'fine_diamond')) {
            Schema::table('player_detentions', function (Blueprint $table): void {
                $table->dropColumn('fine_diamond');
            });
        }

        if (Schema::hasTable('player_detentions') && Schema::hasColumn('player_detentions', 'fine_money')) {
            Schema::table('player_detentions', function (Blueprint $table): void {
                $table->dropColumn('fine_money');
            });
        }
    }
};
