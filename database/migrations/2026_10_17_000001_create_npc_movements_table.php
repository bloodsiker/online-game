<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('npc_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('npc_id')->unique()->constrained('npcs')->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->string('mode', 16)->default('map');
            $table->foreignId('map_id')->nullable()->constrained('maps')->nullOnDelete();
            $table->json('location_ids')->nullable();
            $table->unsignedInteger('interval_minutes')->default(10);
            $table->timestamp('next_move_at')->nullable();
            $table->timestamps();
            $table->index(['enabled', 'next_move_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npc_movements');
    }
};
