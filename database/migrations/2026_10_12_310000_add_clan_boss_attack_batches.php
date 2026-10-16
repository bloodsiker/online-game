<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeons', function (Blueprint $table): void {
            $table->unsignedSmallInteger('clan_boss_hit_batch_size')->default(10)
                ->comment('Ударов в одном пакете на игрока за клановый поход');
            $table->unsignedInteger('clan_boss_hit_batch_cooldown_seconds')->default(21600)
                ->comment('КД после расходования пакета ударов, секунд');
        });

        Schema::create('clan_boss_player_attack_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clan_boss_run_id')->constrained('clan_boss_runs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('hits_in_batch')->default(0);
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();

            $table->unique(['clan_boss_run_id', 'user_id'], 'cb_attack_state_run_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_boss_player_attack_states');

        Schema::table('dungeons', function (Blueprint $table): void {
            $table->dropColumn(['clan_boss_hit_batch_size', 'clan_boss_hit_batch_cooldown_seconds']);
        });
    }
};
