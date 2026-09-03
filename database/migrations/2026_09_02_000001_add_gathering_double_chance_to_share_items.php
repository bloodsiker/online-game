<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Второй бонус инструментов добычи (наряду с gathering_speed_bonus_percent,
 * 2026_08_30_000002) — шанс получить х2 ресурса за одну добычу. Тот же
 * паттерн: обычная колонка на share_items, а не generic ShareItemStatType —
 * инструменты добычи не проходят через PlayerStatService, читаются
 * напрямую в GatheringService по экипированной руке.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->unsignedTinyInteger('gathering_double_chance_percent')->default(0)->after('gathering_speed_bonus_percent');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('gathering_double_chance_percent');
        });
    }
};
