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
            $table->unsignedTinyInteger('minimum_success_chance_percent')
                ->default(5)
                ->after('experience_reward');
        });
    }

    public function down(): void
    {
        Schema::table('share_item_lock_configs', function (Blueprint $table): void {
            $table->dropColumn('minimum_success_chance_percent');
        });
    }
};
