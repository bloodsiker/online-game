<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Мирные профессии для вкладок мастерской инструментов:
 * Колдун и Ювелир (Кулинар — если ещё не создан через админку).
 * Идемпотентно: существующие навыки не дублируются, прогресс не трогается.
 */
return new class extends Migration
{
    private const MAX_LEVEL = 300;

    private const MAX_EXPERIENCE = 1_100_000;

    private const CURVE_POWER = 1.6;

    private const PROFESSIONS = [
        'Колдун' => 'Создаёт чары, амулеты и другие колдовские составы.',
        'Ювелир' => 'Огранивает камни и создаёт украшения.',
        'Кулинар' => 'Готовит еду и напитки с полезными эффектами.',
    ];

    public function up(): void
    {
        foreach (self::PROFESSIONS as $name => $description) {
            $skillId = DB::table('skills')->where('name', $name)->value('id');

            if ($skillId === null) {
                $skillId = DB::table('skills')->insertGetId([
                    'name' => $name,
                    'type' => 'peaceful',
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('skills')->where('id', $skillId)->update([
                    'type' => 'peaceful',
                    'description' => $description,
                    'updated_at' => now(),
                ]);
            }

            DB::table('skill_level_requirements')->upsert(
                $this->requirementsFor((int) $skillId),
                ['skill_id', 'lvl'],
                ['exp_required', 'exp_diff'],
            );
        }
    }

    public function down(): void
    {
        // Профессии и прогресс игроков сохраняем, как в 2026_09_01_000001.
    }

    /** @return list<array{skill_id: int, lvl: int, exp_required: int, exp_diff: int}> */
    private function requirementsFor(int $skillId): array
    {
        $requirements = [];
        $previousExperience = 0;

        for ($level = 1; $level <= self::MAX_LEVEL; $level++) {
            $experience = (int) round(
                100 + (self::MAX_EXPERIENCE - 100) * (($level - 1) / (self::MAX_LEVEL - 1)) ** self::CURVE_POWER,
            );

            $requirements[] = [
                'skill_id' => $skillId,
                'lvl' => $level,
                'exp_required' => $experience,
                'exp_diff' => $experience - $previousExperience,
            ];
            $previousExperience = $experience;
        }

        return $requirements;
    }
};
