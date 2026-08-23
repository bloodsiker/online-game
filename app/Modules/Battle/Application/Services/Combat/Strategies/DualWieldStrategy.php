<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;

class DualWieldStrategy implements AttackStrategyInterface
{
    public function __construct(
        private readonly HitCalculator $hitCalc,
        private readonly FightHitInterface $player,
        private readonly FightHitInterface $monster,
        private readonly ShareItem $leftWeapon,
        private readonly ShareItem $rightWeapon,
    ) {}

    public function getHits(): array
    {
        $hits = [];

        // Левая рука
        $leftHit = $this->hitCalc->hit(
            $this->player,
            $this->monster,
            $this->player->getLeftHandMinDmg(),
            $this->player->getLeftHandMaxDmg()
        );
        $hits[] = $leftHit
            ->setWeaponName($this->leftWeapon->name)
            ->setSkill($this->leftWeapon->skill)
            ->setWeapon($this->leftWeapon)
            ->setHandSide('left');

        // Правая рука
        $rightHit = $this->hitCalc->hit(
            $this->player,
            $this->monster,
            $this->player->getRightHandMinDmg(),
            $this->player->getRightHandMaxDmg()
        );
        $hits[] = $rightHit
            ->setWeaponName($this->rightWeapon->name)
            ->setSkill($this->rightWeapon->skill)
            ->setWeapon($this->rightWeapon)
            ->setHandSide('right');

        return $hits;
    }
}
