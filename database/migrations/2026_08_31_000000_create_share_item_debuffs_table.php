<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_item_debuffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->foreignId('effect_id')->constrained('effects')->cascadeOnDelete();
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();
            $table->unique(['share_item_id', 'effect_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_item_debuffs');
    }
};
