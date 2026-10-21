<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clan_boss_reward_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clan_boss_run_id')->constrained('clan_boss_runs')->cascadeOnDelete();
            $table->foreignId('clan_boss_run_stage_id')->constrained('clan_boss_run_stages')->cascadeOnDelete();
            $table->string('recipient_type', 16);
            $table->unsignedBigInteger('recipient_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_name');
            $table->json('boss_name');
            $table->string('reason', 24);
            $table->json('rewards');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->unique(['clan_boss_run_stage_id', 'recipient_type', 'recipient_id'], 'clan_boss_receipt_recipient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_boss_reward_receipts');
    }
};
