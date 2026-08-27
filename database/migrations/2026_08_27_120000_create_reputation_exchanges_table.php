<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reputation_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->foreignId('reputation_id')->constrained('reputations')->cascadeOnDelete();
            $table->unsignedInteger('points')->default(5);
            // Бракет репутации, в котором этот реликт принимается: [min_reputation, max_reputation)
            $table->unsignedInteger('min_reputation')->default(0);
            $table->unsignedInteger('max_reputation');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reputation_exchanges');
    }
};
