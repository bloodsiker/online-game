<?php

namespace App\Services;

use App\Models\MagicSkill\MagicSkill;
use App\Models\Player\Player;
use Illuminate\Support\Facades\Cache;

class PlayerMagicSkillService
{
    public function getActiveSkillForBattle(Player $player, int $skillId): MagicSkill
    {
        if ($skillId <= 0) {
            throw new \DomainException('Неверный ID заклинание');
        }

        /** @var MagicSkill|null $playerSkill */
        $playerSkill = $player->activeMagicSkills()
            ->where('magic_skill_id', $skillId)
            ->first();

//        if ($this->isSkillOnCooldown($player, $playerSkill)) {
//            $secondsLeft = $this->getSkillCooldownLeft($player, $playerSkill);
//            throw new \DomainException("Перезарядка: ещё {$secondsLeft} сек.");
//        }

        return $playerSkill;
    }

    private function isSkillOnCooldown(Player $player, MagicSkill $skill): bool
    {
        return true;

//        if ($skill->cooldown <= 0) {
//            return false;
//        }
//
//        $lastUsed = Cache::get("skill_cooldown:{$player->id}:{$skill->id}");
//
//        if (!$lastUsed) {
//            return false;
//        }
//
//        return now()->lt($lastUsed->addSeconds($skill->cooldown));
    }

    private function getSkillCooldownLeft(Player $player, Skill $skill): int
    {
        $lastUsed = Cache::get("skill_cooldown:{$player->id}:{$skill->id}");
        if (!$lastUsed) {
            return 0;
        }

        return max(0, $lastUsed->addSeconds($skill->cooldown)->diffInSeconds(now()));
    }

    /**
     * Вызывай после успешного применения скилла
     */
    public function applySkillCooldown(Player $player, MagicSkill $skill): void
    {
//        if ($skill->cooldown > 0) {
//            Cache::put(
//                "skill_cooldown:{$player->id}:{$skill->id}",
//                now(),
//                $skill->cooldown
//            );
//        }
    }
}
