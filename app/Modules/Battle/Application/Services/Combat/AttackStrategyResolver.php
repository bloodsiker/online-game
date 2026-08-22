<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\Services\Combat\Strategies\AttackStrategyInterface;
use App\Modules\Battle\Application\Services\Combat\Strategies\DualWieldStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\FistAttackStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicAttackStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicBuffStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\OneHandWeaponStrategy;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Domain\Services\MonsterCombatantFactory;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Services\MagicCastGuard;
use App\Services\PlayerMagicSkillService;

readonly class AttackStrategyResolver
{
    public function __construct(
        private HitCalculator $hitCalc,
        private MagicHitCalculator $magicHitCalc,
        private MonsterCombatantFactory $combatantFactory,
        private PlayerMagicSkillService $playerMagicSkillService,
        private PlayerStatService $statService,
        private MagicCastGuard $castGuard,
    ) {}

    public function resolve(Player $player, MonsterOnLocation $locMonster, int $action): AttackStrategyInterface
    {
        $monster = $this->combatantFactory->build($locMonster);
        $sheet = $this->statService->resolve($player);

        $equip = $player->playerEquip;
        $left = $equip->handLeft;
        $right = $equip->handRight;

        if ($action > 0) {
            $playerSkill = $this->playerMagicSkillService->getActiveSkillForBattle($player, $action);

            if ($playerSkill instanceof MagicSkill && $playerSkill->isBuffSkill()) {
                return new MagicBuffStrategy(
                    playerModel: $player,
                    magicSkill: $playerSkill,
                );
            }

            return new MagicAttackStrategy(
                magicHitCalc: $this->magicHitCalc,
                castGuard: $this->castGuard,
                player: $sheet,
                playerModel: $player,
                monster: $monster,
                magicSkill: $playerSkill
            );
        }

        // Nothing on — fist
        if (! $left instanceof Item && ! $right instanceof Item) {
            return new FistAttackStrategy(hitCalc: $this->hitCalc, player: $sheet, monster: $monster);
        }

        // Only the shield in the right hand, the left hand is empty — a fist
        if (
            ! $left instanceof Item &&
            $right instanceof Item &&
            $right->itemInfo->type === ShareItemType::SHIELD
        ) {
            return new FistAttackStrategy(hitCalc: $this->hitCalc, player: $sheet, monster: $monster);
        }

        // Weapon in left hand + shield in right hand — only left weapon
        if (
            $left instanceof Item && $left->itemInfo->type === ShareItemType::WEAPON &&
            $right instanceof Item && $right->itemInfo->type === ShareItemType::SHIELD
        ) {
            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                equip: $player->playerEquip
            );
        }

        // Two weapons — dual
        if (
            $left instanceof Item && $left->itemInfo->type === ShareItemType::WEAPON &&
            $right instanceof Item && $right->itemInfo->type === ShareItemType::WEAPON
        ) {
            return new DualWieldStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                leftWeapon: $left->itemInfo,
                rightWeapon: $right->itemInfo
            );
        }

        // Only the right weapon (the left one is empty)
        if (
            ! $left instanceof Item &&
            $right instanceof Item && $right->itemInfo->type === ShareItemType::WEAPON
        ) {
            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                equip: $player->playerEquip
            );
        }

        // Only the left weapon (the right one is empty)
        if (
            $left instanceof Item && $left->itemInfo->type === ShareItemType::WEAPON &&
            ! $right instanceof Item
        ) {
            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                equip: $player->playerEquip
            );
        }

        // Any other case — fist
        return new FistAttackStrategy(hitCalc: $this->hitCalc, player: $sheet, monster: $monster);
    }
}
