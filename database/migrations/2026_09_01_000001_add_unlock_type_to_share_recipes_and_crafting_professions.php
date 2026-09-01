<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MAX_LEVEL = 300;

    private const MAX_EXPERIENCE = 1_100_000;

    private const CURVE_POWER = 1.6;

    private const PROFESSIONS = [
        'Алхимик' => 'Создаёт зелья, эликсиры и другие алхимические составы.',
        'Повар' => 'Готовит еду и напитки с полезными эффектами.',
        'Ремесленник' => 'Изготавливает инструменты, заготовки и расходные материалы.',
    ];

    public function up(): void
    {
        Schema::table('share_recipes', function (Blueprint $table): void {
            $table->string('unlock_type', 24)->default('single_use')->after('percent');
        });

        $peacefulRecipeItemIds = DB::table('share_items')
            ->join('skills', 'skills.id', '=', 'share_items.skill_id')
            ->where('skills.type', 'peaceful')
            ->where('share_items.type', 'recipe')
            ->pluck('share_items.id');

        DB::table('share_recipes')
            ->whereIn('share_item_id', $peacefulRecipeItemIds)
            ->update(['unlock_type' => 'learnable']);

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
        Schema::table('share_recipes', function (Blueprint $table): void {
            $table->dropColumn('unlock_type');
        });

        // Профессии и прогресс игроков сохраняем, чтобы откат схемы рецептов
        // не удалял уже полученный многолетний прогресс.
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
