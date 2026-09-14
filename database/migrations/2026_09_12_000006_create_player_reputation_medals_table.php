<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_reputation_medals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reputation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tier_id')->constrained('reputation_tiers')->cascadeOnDelete();
            $table->boolean('is_feat')->default(false);
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['player_id', 'tier_id', 'is_feat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_reputation_medals');
    }
};
