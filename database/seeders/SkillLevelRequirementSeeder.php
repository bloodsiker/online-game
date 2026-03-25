<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\Skill\SkillLevelRequirement;
use Illuminate\Database\Seeder;

class SkillLevelRequirementSeeder extends Seeder
{
    private const MAX_LEVELS  = 100;
    private const BASE_EXP    = 1000;
    private const GROW_FACTOR = 1.5;
    private const GROUP_SIZE  = 10;

    public function run(): void
    {
        $skills = Skill::all();

        foreach ($skills as $skill) {
            SkillLevelRequirement::where('skill_id', $skill->id)->delete();

            $rows        = [];
            $totalExp    = 0;

            for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
                $group   = intdiv($level - 1, self::GROUP_SIZE);
                $expDiff = (int) round(self::BASE_EXP * pow(self::GROW_FACTOR, $group));
                $totalExp += $expDiff;

                $rows[] = [
                    'skill_id'     => $skill->id,
                    'lvl'          => $level,
                    'exp_required' => $totalExp,
                    'exp_diff'     => $expDiff,
                ];
            }

            SkillLevelRequirement::insert($rows);
        }
    }
}