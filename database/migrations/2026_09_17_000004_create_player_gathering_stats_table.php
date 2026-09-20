<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_gathering_stats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedBigInteger('total_gathered')->default(0);
            $table->timestamps();

            $table->unique(['player_id', 'share_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_gathering_stats');
    }
};
