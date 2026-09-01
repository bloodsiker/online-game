<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Skill\Infrastructure\Persistence\Models\SkillLevelRequirement;

class PeacefulProfessionExperienceService
{
    public function award(Player $player, Skill $skill, int $amount): PlayerSkill
    {
        $playerSkill = PlayerSkill::query()
            ->where('player_id', $player->id)
            ->where('skill_id', $skill->id)
            ->lockForUpdate()
            ->first();

        if ($playerSkill === null) {
            $firstRequirement = SkillLevelRequirement::query()
                ->where('skill_id', $skill->id)
                ->where('lvl', 1)
                ->first();

            $playerSkill = PlayerSkill::query()->create([
                'player_id' => $player->id,
                'skill_id' => $skill->id,
                'lvl' => 1,
                'exp' => 0,
                'exp_up' => $firstRequirement?->exp_required ?? 100,
                'exp_diff' => $firstRequirement?->exp_diff ?? 100,
            ]);
        }

        $playerSkill->exp += max(1, $amount);

        while ($playerSkill->exp >= $playerSkill->exp_up) {
            $nextLevel = $playerSkill->lvl + 1;
            $requirement = SkillLevelRequirement::query()
                ->where('skill_id', $skill->id)
                ->where('lvl', $nextLevel)
                ->first();

            if ($requirement === null) {
                break;
            }

            $playerSkill->lvl = $nextLevel;
            $playerSkill->exp_up = $requirement->exp_required;
            $playerSkill->exp_diff = $requirement->exp_diff;
        }

        $playerSkill->save();

        return $playerSkill;
    }
}
