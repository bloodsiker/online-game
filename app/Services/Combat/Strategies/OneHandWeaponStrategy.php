<?php

namespace App\Services\Combat\Strategies;

use App\Models\Item\Item;
use App\Models\Monster\Monster;
use App\Models\Player\PlayerEquipment;
use App\Services\Combat\HitCalculator;
use App\Services\Combat\FightHitInterface;

class OneHandWeaponStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private FightHitInterface $player,
        private Monster $monster,
        private PlayerEquipment $equip,
    ) {}

    public function getHits(): array
    {
        $itemInfoLeft = $this->equip->handLeft instanceof Item;
        $activeWeapon = $itemInfoLeft ? $this->equip->handLeft : $this->equip->handRight;

        $min = $itemInfoLeft
            ? $this->player->getLeftHandMinDmg()
            : $this->player->getRightHandMinDmg();

        $max = $itemInfoLeft
            ? $this->player->getLeftHandMaxDmg()
            : $this->player->getRightHandMaxDmg();

        $hit = $this->hitCalc->playerHit($this->player, $this->monster, $min, $max);

        return [
            $hit
                ->setWeaponName($activeWeapon->itemInfo->name)
                ->setSkill($activeWeapon->itemInfo->skill)
                ->setWeapon($activeWeapon->itemInfo)
        ];
    }
}
