<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->nullable()->constrained('players')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot_number');
            $table->enum('entity_type', ['item', 'skill']);
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();

            $table->unique(['player_id', 'slot_number']);
            $table->index(['player_id', 'entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_slots');
    }
};
