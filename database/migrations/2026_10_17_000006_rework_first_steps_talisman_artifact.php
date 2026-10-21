<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Переделка «Талисмана Первых Шагов» (id 1834, см.
 * 2026_10_17_000004_seed_starter_legendary_artifact_pendants.php) — по
 * прямому указанию пользователя: убрать bag_slot, вместо него — такой же
 * широкий разброс «понемногу», что и у «Печати Пробуждения» (id 1833, см.
 * 2026_10_17_000005_rework_awakening_seal_artifact.php), но из другого
 * вокабуляра статов (endurance/magic_resistance/agility/intuition/
 * crit_damage вместо armor/dodge/critical/attack_min/max/magic_attack/
 * wisdom/intelligence у Печати) — два стартовых артефакта дополняют друг
 * друга, а не дублируют один и тот же набор. hp_max остаётся сигнатурным
 * статом талисмана (чуть меньше прежнего — бюджет теперь расходится на 6
 * статов вместо 2).
 */
return new class extends Migration
{
    private const ITEM_ID = 1834;

    private const OLD_STATS = [
        ['type' => 'hp_max', 'value' => 40],
        ['type' => 'bag_slot', 'value' => 3],
    ];

    private const NEW_STATS = [
        ['type' => 'hp_max', 'value' => 35],
        ['type' => 'endurance', 'value' => 8],
        ['type' => 'magic_resistance', 'value' => 8],
        ['type' => 'agility', 'value' => 8],
        ['type' => 'intuition', 'value' => 8],
        ['type' => 'crit_damage', 'value' => 8],
    ];

    public function up(): void
    {
        $this->replaceStats(self::NEW_STATS);
    }

    public function down(): void
    {
        $this->replaceStats(self::OLD_STATS);
    }

    /** @param  list<array{type: string, value: int}>  $stats */
    private function replaceStats(array $stats): void
    {
        $now = now();

        DB::table('share_item_stats')->where('share_item_id', self::ITEM_ID)->delete();

        foreach ($stats as $stat) {
            DB::table('share_item_stats')->insert([
                'share_item_id' => self::ITEM_ID,
                'stat_type' => $stat['type'],
                'value' => $stat['value'],
                'value_type' => 'flat',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
