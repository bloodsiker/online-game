<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_event_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('world_event_id')->constrained('world_events')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'world_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_event_favorites');
    }
};
