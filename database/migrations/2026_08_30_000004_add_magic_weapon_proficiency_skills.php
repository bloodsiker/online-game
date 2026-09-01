<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Три навыка владения магическим оружием — по одному на архетип
 * (docs/superpowers/specs/2026-08-30-magic-weapons-design.md), симметрично
 * тому, как физическое оружие разнесено по «Стегающее»/«Рубящее», а не
 * свалено в один общий навык. Мирроринг 2026_08_12_120000_add_shield_mastery_skill.php:
 * та же линейная формула опыта, тот же skill_exp=2 за удар, что у обычного
 * оружия (не 7, как у щита — тут нет вероятностного триггера вроде блока).
 *
 * Гейт (share_item_requirements) НЕ добавляется: все три предмета —
 * стартовый common-тир (см. дагу «Кинжал «Ночной бури»» — тоже без гейта),
 * гейтить нечем, пока не появятся более редкие тиры магического оружия.
 */
return new class extends Migration
{
    private const SKILLS = [
        'Посох Пепельной Башни' => 'Владение посохом',
        'Жезл Пепельной Башни' => 'Владение жезлом',
        'Сфера Пепельной Башни' => 'Владение сферой',
    ];

    private const SKILL_TYPE = 'combat';

    /** Опыт навыка за удар — тот же темп, что у обычного оружия (не щитовой 7) */
    private const WEAPON_SKILL_EXP = 2;

    private const MAX_LEVELS = 2000;

    private const BASE_EXP = 18;

    public function up(): void
    {
        foreach (self::SKILLS as $itemName => $skillName) {
            $itemId = DB::table('share_items')->where('name', $itemName)->value('id');

            if ($itemId === null) {
                continue;
            }

            $skillId = $this->ensureSkill($skillName);
            $this->seedLevelRequirements($skillId);

            DB::table('share_items')->where('id', $itemId)->update([
                'skill_id' => $skillId,
                'skill_exp' => self::WEAPON_SKILL_EXP,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (self::SKILLS as $skillName) {
            $skillId = DB::table('skills')->where('name', $skillName)->value('id');

            if ($skillId === null) {
                continue;
            }

            DB::table('share_items')->where('skill_id', $skillId)->update([
                'skill_id' => null,
                'skill_exp' => null,
            ]);

            // player_skills и skill_level_requirements уйдут каскадом по skill_id
            DB::table('skills')->where('id', $skillId)->delete();
        }
    }

    private function ensureSkill(string $name): int
    {
        $existing = DB::table('skills')->where('name', $name)->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return (int) DB::table('skills')->insertGetId([
            'name' => $name,
            'type' => self::SKILL_TYPE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Та же линейная формула, что у «Владение щитом» и SkillLevelRequirementSeeder */
    private function seedLevelRequirements(int $skillId): void
    {
        if (DB::table('skill_level_requirements')->where('skill_id', $skillId)->exists()) {
            return;
        }

        $rows = [];
        $totalExp = 0;

        for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
            $expDiff = (int) round(self::BASE_EXP * $level);
            $totalExp += $expDiff;

            $rows[] = [
                'skill_id' => $skillId,
                'lvl' => $level,
                'exp_required' => $totalExp,
                'exp_diff' => $expDiff,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('skill_level_requirements')->insert($chunk);
        }
    }
};
