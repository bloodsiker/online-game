<?php

namespace App\Services\Combat\Strategies;

use App\Models\Monster\Monster;
use App\Models\Skill;
use App\Services\Combat\HitCalculator;
use App\Services\Combat\FightHitInterface;

class FistAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private FightHitInterface $player,
        private Monster $monster
    ) {}

    public function getHits(): array
    {
        $skill = Skill::find(Skill::SKILL_HAND_ID);
        $hit = $this->hitCalc->playerHit(
            $this->player,
            $this->monster,
            $this->player->min_dmg,
            $this->player->max_dmg
        );

        return [$hit->setWeaponName('кулаком')->setSkill($skill)];
    }
}
