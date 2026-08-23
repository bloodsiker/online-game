<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;

class OneHandWeaponStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private FightHitInterface $player,
        private FightHitInterface $monster,
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

        $hit = $this->hitCalc->hit($this->player, $this->monster, $min, $max);

        return [
            $hit
                ->setWeaponName($activeWeapon->itemInfo->name)
                ->setSkill($activeWeapon->itemInfo->skill)
                ->setWeapon($activeWeapon->itemInfo)
                ->setHandSide($itemInfoLeft ? 'left' : 'right'),
        ];
    }
}
