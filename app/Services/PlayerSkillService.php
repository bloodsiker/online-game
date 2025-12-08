<?php

namespace app\Services;

use App\Models\Player\Player;
use App\Models\Player\PlayerSkill;
use App\Models\ShareItem;
use App\Models\Skill;

class PlayerSkillService
{
    public function gainExperienceSkill(Player $player, ?Skill $skill = null, ?ShareItem $shareItem = null): void
    {
        if ($skill) {
            $playerSkill = PlayerSkill::where(['player_id' => $player->id, 'skill_id' => $skill->id])->first();
            if ($playerSkill) {
                $expPerHit = $shareItem && $shareItem->skill_exp ? $shareItem->skill_exp : 1;
                $playerSkill->exp += $expPerHit;

                if ($playerSkill->exp >= $playerSkill->exp_up) {
                    $playerSkill->lvl += 1;
                    $playerSkill->exp = 0;
                    $playerSkill->exp_up = $this->calculateRequiredExperience($playerSkill->lvl);
                }

                $playerSkill->save();
            } else {
                $playerSkill = new PlayerSkill();
                $playerSkill->player_id = $player->id;
                $playerSkill->skill_id = $skill->id;
                $playerSkill->save();
            }
        }
    }

    protected function calculateRequiredExperience($level) :int
    {
        // Уровни делятся на группы по 10, каждая группа имеет свой базовый опыт
        $levelGroup = intdiv($level - 1, 10); // Количество завершенных групп по 10 уровней

        return PlayerSkill::BASE_EXPERIENCE * pow(PlayerSkill::GROW_FACTOR, $levelGroup);
    }
}
