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
        Schema::create('share_item_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->onDelete('cascade');
            $table->enum('effect_type', ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp', 'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot']);
            $table->integer('value');
            $table->enum('value_type', ['flat', 'percent']);
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Длительность бафа в секундах. NULL = мгновенный эффект.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_item_effects');
    }
};
