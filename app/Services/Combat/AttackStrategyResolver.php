<?php

namespace App\Services\Combat;

use App\Decorator\Player\BuffDecorator;
use App\Decorator\Player\EquipmentDecorator;
use App\Models\Item\Item;
use App\Models\Monster\Monster;
use App\Models\Player\Player;
use App\Models\ShareItem;
use App\Services\Combat\Strategies\AttackStrategyInterface;
use App\Services\Combat\Strategies\FistAttackStrategy;
use App\Services\Combat\Strategies\DualWieldStrategy;
use App\Services\Combat\Strategies\MagicAttackStrategy;
use App\Services\Combat\Strategies\OneHandWeaponStrategy;
use App\Services\PlayerMagicSkillService;

readonly class AttackStrategyResolver
{
    public function __construct(
        private HitCalculator $hitCalc,
        private PlayerMagicSkillService $playerMagicSkillService,
    ) {}

    public function resolve(Player $player, Monster $monster, int $action): AttackStrategyInterface
    {
        $playerDecorator = new EquipmentDecorator($player);
        $playerDecorator = new BuffDecorator($playerDecorator);

        $equip = $player->playerEquip;
        $left = $equip->handLeft;
        $right = $equip->handRight;

        if ($action > 0) {
            $playerSkill = $this->playerMagicSkillService->getActiveSkillForBattle($player, $action);

            return new MagicAttackStrategy(
                hitCalc: $this->hitCalc,
                player: $player,
                monster: $monster,
                magicSkill: $playerSkill
            );
        }

        // Nothing on — fist
        if (!$left instanceof Item && !$right instanceof Item) {
            return new FistAttackStrategy(hitCalc: $this->hitCalc, player: $player, monster: $monster);
        }

        // Only the shield in the right hand, the left hand is empty — a fist
        if (
            !$left instanceof Item &&
            $right instanceof Item &&
            $right->itemInfo->type === ShareItem::TYPE_SHIELD
        ) {
            return new FistAttackStrategy(hitCalc: $this->hitCalc, player: $player, monster: $monster);
        }

        // Weapon in left hand + shield in right hand — only left weapon
        if (
            $left instanceof Item && $left->itemInfo->type === ShareItem::TYPE_WEAPON &&
            $right instanceof Item && $right->itemInfo->type === ShareItem::TYPE_SHIELD
        ) {
            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $playerDecorator,
                monster: $monster,
                equip: $player->playerEquip
            );
        }

        // Two weapons — dual
        if (
            $left instanceof Item && $left->itemInfo->type === ShareItem::TYPE_WEAPON &&
            $right instanceof Item && $right->itemInfo->type === ShareItem::TYPE_WEAPON
        ) {
            return new DualWieldStrategy(
                hitCalc: $this->hitCalc,
                player: $playerDecorator,
                monster: $monster,
                leftWeapon: $left->itemInfo,
                rightWeapon: $right->itemInfo
            );
        }

        // Only the right weapon (the left one is empty)
        if (
            !$left instanceof Item &&
            $right instanceof Item && $right->itemInfo->type === ShareItem::TYPE_WEAPON
        ) {
            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $playerDecorator,
                monster: $monster,
                equip: $player->playerEquip
            );
        }

        // Only the left weapon (the right one is empty)
        if (
            $left instanceof Item && $left->itemInfo->type === ShareItem::TYPE_WEAPON &&
            !$right instanceof Item
        ) {
            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $playerDecorator,
                monster: $monster,
                equip: $player->playerEquip
            );
        }

        // Any other case (e.g., shield + something that is not a weapon) — fist
        return new FistAttackStrategy(hitCalc: $this->hitCalc, player: $player, monster: $monster);
    }
}
