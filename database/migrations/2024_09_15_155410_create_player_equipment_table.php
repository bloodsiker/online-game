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
        Schema::create('player_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('hand_left')->nullable()->comment('Левая рука')->constrained('items')->nullOnDelete();
            $table->foreignId('hand_right')->nullable()->comment('Правая рука')->constrained('items')->nullOnDelete();
            $table->foreignId('helmet')->nullable()->comment('Шлем')->constrained('items')->nullOnDelete();
            $table->foreignId('shoulder')->nullable()->comment('Наплечники')->constrained('items')->nullOnDelete();
            $table->foreignId('forearm')->nullable()->comment('Наручии')->constrained('items')->nullOnDelete();
            $table->foreignId('armor')->nullable()->comment('Броня')->constrained('items')->nullOnDelete();
            $table->foreignId('legging')->nullable()->comment('Поножи')->constrained('items')->nullOnDelete();
            $table->foreignId('chain_armor')->nullable()->comment('Кольчуга')->constrained('items')->nullOnDelete();
            $table->foreignId('cloak')->nullable()->comment('Накидка')->constrained('items')->nullOnDelete();
            $table->foreignId('shoes')->nullable()->comment('Обувь')->constrained('items')->nullOnDelete();
            $table->foreignId('gloves')->nullable()->comment('Перчатки')->constrained('items')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_equipments');
    }
};
