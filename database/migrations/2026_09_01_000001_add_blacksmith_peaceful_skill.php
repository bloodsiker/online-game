<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Пятая мирная профессия — Кузнец. Плавит руду, куёт заготовки и огранивает
 * самоцветы: крафтит материалы возвышения (Осколок/Кристалл/Сердце/Реликтовое
 * ядро — 2026-09-01, Кузня structure id=8) и предметы у кузницы вообще.
 *
 * Кривая опыта — та же формула, что уже применена ко всем существующим
 * мирным навыкам в 2026_08_30_000003_extend_peaceful_profession_progression
 * (MAX_LEVEL=300, экспонента 1.6, потолок 1_100_000 опыта на 300 уровне),
 * чтобы новый навык сразу был на одной кривой со старыми, а не стартовал со
 * старого 100-уровневого шаблона (2026_08_29_000003).
 */
return new class extends Migration
{
    private const SKILL_NAME = 'Кузнец';

    private const MAX_LEVEL = 300;

    private const MAX_EXPERIENCE = 1_100_000;

    private const CURVE_POWER = 1.6;

    public function up(): void
    {
        $skillId = DB::table('skills')->where('name', self::SKILL_NAME)->value('id');

        if ($skillId === null) {
            $skillId = DB::table('skills')->insertGetId([
                'name' => self::SKILL_NAME,
                'type' => 'peaceful',
                'description' => 'Плавит руду, куёт заготовки и огранивает самоцветы — крафтит материалы возвышения и снаряжение у кузницы.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('skill_level_requirements')->where('skill_id', $skillId)->exists()) {
            return;
        }

        $rows = [];
        $previous = 0;
        for ($level = 1; $level <= self::MAX_LEVEL; $level++) {
            $experience = (int) round(
                100 + (self::MAX_EXPERIENCE - 100) * (($level - 1) / (self::MAX_LEVEL - 1)) ** self::CURVE_POWER,
            );

            $rows[] = [
                'skill_id' => $skillId,
                'lvl' => $level,
                'exp_required' => $experience,
                'exp_diff' => $experience - $previous,
            ];
            $previous = $experience;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('skill_level_requirements')->insert($chunk);
        }
    }

    public function down(): void
    {
        // Прогресс игроков не стираем — тот же осознанный выбор, что у
        // остальных мирных профессий в этой сессии.
    }
};
