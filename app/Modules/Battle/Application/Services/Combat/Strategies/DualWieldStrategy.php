<?php

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;

class DualWieldStrategy implements AttackStrategyInterface
{
    public function __construct(
        private readonly HitCalculator $hitCalc,
        private readonly FightHitInterface $player,
        private readonly Monster $monster,
        private readonly ShareItem $leftWeapon,
        private readonly ShareItem $rightWeapon,
    ) {}

    public function getHits(): array
    {
        $hits = [];

        // Левая рука
        $leftHit = $this->hitCalc->playerHit(
            $this->player,
            $this->monster,
            $this->player->getLeftHandMinDmg(),
            $this->player->getLeftHandMaxDmg()
        );
        $hits[] = $leftHit
            ->setWeaponName($this->leftWeapon->name)
            ->setSkill($this->leftWeapon->skill)
            ->setWeapon($this->leftWeapon);

        // Правая рука
        $rightHit = $this->hitCalc->playerHit(
            $this->player,
            $this->monster,
            $this->player->getRightHandMinDmg(),
            $this->player->getRightHandMaxDmg()
        );
        $hits[] = $rightHit
            ->setWeaponName($this->rightWeapon->name)
            ->setSkill($this->rightWeapon->skill)
            ->setWeapon($this->rightWeapon);

        return $hits;
    }
}
