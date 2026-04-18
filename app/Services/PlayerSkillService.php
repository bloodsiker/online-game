<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Share\ShareItem;
use App\Models\Skill;
use App\Models\Skill\SkillLevelRequirement;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;

class PlayerSkillService
{
    public function gainExperienceSkill(Player $player, ?Skill $skill = null, ?ShareItem $shareItem = null): void
    {
        if (! $skill) {
            return;
        }

        $playerSkill = PlayerSkill::where(['player_id' => $player->id, 'skill_id' => $skill->id])->first();

        if (! $playerSkill) {
            $req = $this->getRequirement($skill->id, 1);

            $playerSkill = new PlayerSkill;
            $playerSkill->player_id = $player->id;
            $playerSkill->skill_id = $skill->id;
            $playerSkill->exp = 0;
            $playerSkill->exp_up = $req?->exp_required ?? 1000;
            $playerSkill->exp_diff = $req?->exp_diff ?? 1000;
            $playerSkill->save();

            return;
        }

        $expPerHit = ($shareItem && $shareItem->skill_exp) ? $shareItem->skill_exp : 1;
        $playerSkill->exp += $expPerHit;

        if ($playerSkill->exp >= $playerSkill->exp_up) {
            $playerSkill->lvl += 1;

            $req = $this->getRequirement($skill->id, $playerSkill->lvl);
            if ($req) {
                $playerSkill->exp_up = $req->exp_required;
                $playerSkill->exp_diff = $req->exp_diff;
            }
        }

        $playerSkill->save();
    }

    private function getRequirement(int $skillId, int $level): ?object
    {
        return SkillLevelRequirement::where('skill_id', $skillId)
            ->where('lvl', $level)
            ->first(['exp_required', 'exp_diff']);
    }
}
