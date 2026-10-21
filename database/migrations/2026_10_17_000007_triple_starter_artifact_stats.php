<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ×3 ко всем статам обоих стартовых артефактов (id 1833 «Печать
 * Пробуждения», id 1834 «Талисман Первых Шагов») — по прямому указанию
 * пользователя, после проверки реального бюджета: 1 очко предметного стата
 * (dodge/critical/armor/magic_resistance) конвертируется 1:1 без softcap
 * (см. PlayerStatFormulas::DODGE_PER_AGILITY и соседние константы = 1,
 * HP_PER_ENDURANCE = 3), игрок получает ~10 сырых очков статов за уровень
 * (5 free_stats + ~5 автоприроста расы, см. RecalculatePlayerStats). Старый
 * суммарный бюджет (~82 и ~75 единиц) соответствовал ~8 уровням вложений —
 * пользователь счёл это слишком скромным для платного легендарного
 * стартового предмета и попросил утроить, доведя бюджет примерно до
 * 24 уровней вложений.
 */
return new class extends Migration
{
    private const MULTIPLIER = 3;

    private const ITEM_IDS = [1833, 1834];

    public function up(): void
    {
        $this->scale(self::MULTIPLIER);
    }

    public function down(): void
    {
        $this->scale(1 / self::MULTIPLIER);
    }

    private function scale(float $factor): void
    {
        $now = now();

        $rows = DB::table('share_item_stats')
            ->whereIn('share_item_id', self::ITEM_IDS)
            ->get(['id', 'value']);

        foreach ($rows as $row) {
            DB::table('share_item_stats')
                ->where('id', $row->id)
                ->update([
                    'value' => (int) round($row->value * $factor),
                    'updated_at' => $now,
                ]);
        }
    }
};
