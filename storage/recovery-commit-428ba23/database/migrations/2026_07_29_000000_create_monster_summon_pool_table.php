<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monster_summon_pool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained('monsters')->cascadeOnDelete();
            $table->foreignId('minion_monster_id')->constrained('monsters')->cascadeOnDelete();
            $table->unsignedInteger('weight')->default(1);
            $table->timestamps();

            $table->index(['monster_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monster_summon_pool');
    }
};