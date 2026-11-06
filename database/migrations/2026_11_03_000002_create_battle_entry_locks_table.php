<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No FK to locations: finishing a battle updates that row, so using it as the entry mutex can deadlock.
        Schema::create('battle_entry_locks', fn (Blueprint $table) => $table->unsignedBigInteger('location_id')->primary());
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_entry_locks');
    }
};
