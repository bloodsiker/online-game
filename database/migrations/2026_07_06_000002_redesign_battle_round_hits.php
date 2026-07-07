<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battle_rounds', function (Blueprint $table): void {
            $table->dropColumn(['player_hp_before', 'player_mp_before']);
        });

        Schema::dropIfExists('battle_round_hits');

        Schema::create('battle_round_hits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_round_id');
            $table->enum('participant_type', ['user', 'monster']);
            $table->unsignedBigInteger('participant_id');
            $table->unsignedInteger('hp_after')->nullable();
            $table->text('action');
            $table->timestamps();

            $table->foreign('battle_round_id')
                ->references('id')->on('battle_rounds')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_round_hits');

        Schema::table('battle_rounds', function (Blueprint $table): void {
            $table->unsignedInteger('player_hp_before')->nullable()->after('action');
            $table->unsignedInteger('player_mp_before')->nullable()->after('player_hp_before');
        });
    }
};