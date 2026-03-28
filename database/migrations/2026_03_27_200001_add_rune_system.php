<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Рунные слоты на предмете (0–3, только оружие/щит)
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedTinyInteger('rune_slot_count')->default(0)->after('socket_count');
        });

        // Вставленные руны с прокрученными статами
        Schema::create('item_runes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot_index'); // 0, 1, 2
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            // [{type, value, is_percent, locked}]
            $table->json('stats');
            // {type, value, description} — null если нет пассивки
            $table->json('passive_skill')->nullable();
            $table->unsignedSmallInteger('reroll_count')->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'slot_index']);
        });

        // Редкость руны и пул статов на share_items
        Schema::table('share_items', function (Blueprint $table) {
            $table->string('rune_rarity', 20)->nullable()->after('gem_stats');
            // JSON array of stat type keys this rune can roll, null = full pool
            $table->json('rune_stat_pool')->nullable()->after('rune_rarity');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table) {
            $table->dropColumn(['rune_rarity', 'rune_stat_pool']);
        });

        Schema::dropIfExists('item_runes');

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('rune_slot_count');
        });
    }
};