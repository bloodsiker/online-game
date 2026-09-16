<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_item_lock_configs', function (Blueprint $table): void {
            $table->unsignedSmallInteger('experience_reward')->nullable()->after('lock_duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('share_item_lock_configs', function (Blueprint $table): void {
            $table->dropColumn('experience_reward');
        });
    }
};
