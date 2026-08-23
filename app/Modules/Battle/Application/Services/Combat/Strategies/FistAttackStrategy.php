<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Models\Skill;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Player\Domain\DTO\StatSheet;

class FistAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private StatSheet $player,
        private FightHitInterface $monster
    ) {}

    public function getHits(): array
    {
        $skill = Skill::find(Skill::SKILL_HAND_ID);
        $hit = $this->hitCalc->hit(
            $this->player,
            $this->monster,
            $this->player->getLeftHandMinDmg(),
            $this->player->getLeftHandMaxDmg()
        );

        return [$hit->setWeaponName('кулаком')->setSkill($skill)];
    }
}
