<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reputation_gamble_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reputation_id')->constrained('reputations')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedInteger('resource_cost');
            $table->unsignedTinyInteger('success_chance');
            $table->unsignedInteger('reward_points');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['reputation_id', 'share_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reputation_gamble_options');
    }
};
