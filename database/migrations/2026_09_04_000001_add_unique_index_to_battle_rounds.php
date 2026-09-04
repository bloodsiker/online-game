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
            $table->unique(['battle_id', 'round_number'], 'battle_rounds_battle_round_unique');
        });
    }

    public function down(): void
    {
        Schema::table('battle_rounds', function (Blueprint $table): void {
            $table->dropUnique('battle_rounds_battle_round_unique');
        });
    }
};
