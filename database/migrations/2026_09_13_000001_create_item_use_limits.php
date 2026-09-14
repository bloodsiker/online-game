<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_item_use_limits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_item_id')->unique()->constrained('share_items')->cascadeOnDelete();
            $table->unsignedSmallInteger('max_uses');
            $table->unsignedInteger('period_seconds');
            $table->timestamps();
        });

        Schema::create('player_item_use_limit_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('share_item_use_limit_id')->constrained('share_item_use_limits')->cascadeOnDelete();
            $table->timestamp('period_started_at');
            $table->unsignedSmallInteger('uses_count')->default(0);
            $table->timestamps();

            $table->unique(
                ['player_id', 'share_item_use_limit_id'],
                'player_item_use_limit_unique',
            );
        });

        Schema::table('share_item_buffs', function (Blueprint $table): void {
            $table->string('reapply_policy', 20)->default('refresh')->after('duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('share_item_buffs', function (Blueprint $table): void {
            $table->dropColumn('reapply_policy');
        });

        Schema::dropIfExists('player_item_use_limit_states');
        Schema::dropIfExists('share_item_use_limits');
    }
};
