<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Skill\Infrastructure\Persistence\Models\SkillLevelRequirement;
use Illuminate\Support\Facades\DB;

class PlayerSkillService
{
    public function gainExperienceSkill(Player $player, ?Skill $skill = null, ?ShareItem $shareItem = null): void
    {
        if (! $skill) {
            return;
        }

        DB::transaction(function () use ($player, $skill, $shareItem): void {
            Player::whereKey($player->id)->lockForUpdate()->firstOrFail();
            $req = $this->getRequirement($skill->id, 1);
            $playerSkill = PlayerSkill::query()->lockForUpdate()->firstOrCreate(
                attributes: ['player_id' => $player->id, 'skill_id' => $skill->id],
                values: ['exp' => 0, 'exp_up' => $req?->exp_required ?? 1000, 'exp_diff' => $req?->exp_diff ?? 1000],
            );
            if ($playerSkill->wasRecentlyCreated) {
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
        });
    }

    private function getRequirement(int $skillId, int $level): ?object
    {
        return SkillLevelRequirement::where('skill_id', $skillId)
            ->where('lvl', $level)
            ->first(['exp_required', 'exp_diff']);
    }
}
