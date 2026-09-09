<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('share_item_id')->comment('Тип артефакта — у игрока не может быть двух надетых одного типа')->constrained('share_items')->cascadeOnDelete();
            $table->foreignId('item_id')->comment('Конкретный экземпляр из рюкзака')->constrained('items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['player_id', 'share_item_id']);
            $table->unique('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_artifacts');
    }
};
