<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PASSIVE_TYPES = ['attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'agility', 'intuition',
        'wisdom', 'intelligence', 'dodge', 'critical', 'magic_attack', 'hp_max'];

    public function up(): void
    {
        Schema::create('share_item_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->onDelete('cascade');
            $table->enum('stat_type', self::PASSIVE_TYPES);
            $table->integer('value');
            $table->enum('value_type', ['flat', 'percent']);
            $table->timestamps();

            $table->index('share_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_item_stats');
    }
};
