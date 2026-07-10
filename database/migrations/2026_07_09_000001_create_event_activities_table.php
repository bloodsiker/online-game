<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_activities', function (Blueprint $table) {
            $table->id();
            $table->enum('period', ['daily', 'weekly']);
            $table->string('title');
            $table->text('description');

            $table->foreignId('monster_id')->nullable()->constrained('monsters')->nullOnDelete();
            $table->unsignedInteger('required_count')->default(1);

            $table->foreignId('reward_share_item_id')->constrained('share_items');
            $table->unsignedInteger('reward_item_amount')->default(1);

            $table->enum('bonus_reward_type', ['money', 'diamond', 'item'])->nullable();
            $table->unsignedInteger('bonus_reward_amount')->nullable();
            $table->foreignId('bonus_reward_share_item_id')->nullable()->constrained('share_items')->nullOnDelete();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['period', 'sort_order']);
        });

        Schema::create('event_activity_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_activity_id')->constrained('event_activities')->cascadeOnDelete();

            // Идентификатор цикла: дата (Y-m-d) для дневных, год-неделя (o-\WW) для недельных —
            // разделяет прогресс на периоды, не давая старому прогрессу засчитаться в новом цикле.
            $table->string('period_key');

            $table->unsignedInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reward_claimed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_activity_id', 'period_key'], 'event_activity_progresses_unique');
            $table->index(['user_id', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_activity_progresses');
        Schema::dropIfExists('event_activities');
    }
};
