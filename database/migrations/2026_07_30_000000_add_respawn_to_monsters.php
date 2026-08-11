<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table) {
            $table->unsignedInteger('respawn_min_minutes')->nullable()->after('is_boss');
            $table->unsignedInteger('respawn_max_minutes')->nullable()->after('respawn_min_minutes');
            $table->timestamp('respawn_at')->nullable()->after('respawn_max_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table) {
            $table->dropColumn(['respawn_min_minutes', 'respawn_max_minutes', 'respawn_at']);
        });
    }
};