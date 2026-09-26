<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_clan_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('quest_stages')->nullOnDelete();
            $table->string('status')->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();

            // One active quest per clan
            $table->index('clan_id', 'qcp_clan_id_idx');
            $table->unique(['user_id', 'quest_id', 'status']);
        });

        Schema::create('quest_clan_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_clan_progress_id')->constrained('quest_clan_progress')->cascadeOnDelete();
            $table->foreignId('quest_objective_id')->constrained('quest_objectives')->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_clan_objectives');
        Schema::dropIfExists('quest_clan_progress');
    }
};
