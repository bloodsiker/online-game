<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сокеты на конкретном предмете (0–3)
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedTinyInteger('socket_count')->default(0)->after('upgrade_fail_streak');
        });

        // Вставленные камни в предмет
        Schema::create('item_gems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedTinyInteger('socket_index'); // 0, 1, 2
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['item_id', 'socket_index']);
        });

        // Параметры камня на share_items (заполняется только для type=gem)
        Schema::table('share_items', function (Blueprint $table) {
            $table->json('gem_stats')->nullable()->after('upgrade_scroll_type');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table) {
            $table->dropColumn('gem_stats');
        });

        Schema::dropIfExists('item_gems');

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('socket_count');
        });
    }
};