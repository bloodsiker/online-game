<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\Services\Combat\Strategies\AttackStrategyInterface;
use App\Modules\Battle\Application\Services\Combat\Strategies\DualWieldStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\FistAttackStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicAttackStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicBuffStrategy;
use App\Modules\Battle\Application\Services\Combat\Strategies\OneHandWeaponStrategy;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\MagicSkill\Application\Services\MagicCastGuard;
use App\Modules\MagicSkill\Application\Services\PlayerMagicSkillService;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Domain\Services\MonsterCombatantFactory;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;

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

    public function resolve(Player $player, MonsterOnLocation $locMonster, int $action, Battle $battle): AttackStrategyInterface
    {
        // Магические эффекты хранятся на конкретном спавне моба и истекают по
        // реальному времени, поэтому действуют и после завершения боя.
        $monster = $this->combatantFactory->build($locMonster);
        $sheet = $this->statService->resolve($player);

        $equip = $player->playerEquip;
        $left = $equip->handLeft;
        $right = $equip->handRight;

        if ($action > 0) {
            $playerSkill = $this->playerMagicSkillService->getActiveSkillForBattle($player, $action);

            if ($playerSkill instanceof MagicSkill && $playerSkill->isBuffSkill()) {
                return new MagicBuffStrategy(
                    castGuard: $this->castGuard,
                    magicHitCalc: $this->magicHitCalc,
                    casterSheet: $sheet,
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

        $leftIsWeapon = $left instanceof Item && $left->itemInfo->type === ShareItemType::WEAPON;
        $rightIsWeapon = $right instanceof Item && $right->itemInfo->type === ShareItemType::WEAPON;
        $rightIsShield = $right instanceof Item && $right->itemInfo->type === ShareItemType::SHIELD;

        // Пустые руки, щиты и инструменты не считаются оружием.
        if (! $leftIsWeapon && ! $rightIsWeapon) {
            $skillLevels = $this->playerSkillLevels($player, [Skill::SKILL_HAND_ID]);

            return new FistAttackStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                weaponSkillLevel: $this->skillLevelFrom($skillLevels, Skill::SKILL_HAND_ID),
            );
        }

        // Weapon in left hand + shield in right hand — only left weapon
        if ($leftIsWeapon && $rightIsShield) {
            $skillId = $left->itemInfo->skill_id;
            $skillLevels = $this->playerSkillLevels($player, [$skillId]);

            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                equip: $player->playerEquip,
                weaponSkillLevel: $this->skillLevelFrom($skillLevels, $skillId),
            );
        }

        // Two weapons — dual
        if ($leftIsWeapon && $rightIsWeapon) {
            $leftSkillId = $left->itemInfo->skill_id;
            $rightSkillId = $right->itemInfo->skill_id;
            $skillLevels = $this->playerSkillLevels($player, [$leftSkillId, $rightSkillId]);

            return new DualWieldStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                leftWeapon: $left->itemInfo,
                rightWeapon: $right->itemInfo,
                leftWeaponSkillLevel: $this->skillLevelFrom($skillLevels, $leftSkillId),
                rightWeaponSkillLevel: $this->skillLevelFrom($skillLevels, $rightSkillId),
            );
        }

        // Одно оружие; во второй руке может быть инструмент.
        if ($leftIsWeapon || $rightIsWeapon) {
            $skillId = ($leftIsWeapon ? $left : $right)->itemInfo->skill_id;
            $skillLevels = $this->playerSkillLevels($player, [$skillId]);

            return new OneHandWeaponStrategy(
                hitCalc: $this->hitCalc,
                player: $sheet,
                monster: $monster,
                equip: $player->playerEquip,
                weaponSkillLevel: $this->skillLevelFrom($skillLevels, $skillId),
            );
        }

        // Защитный fallback для неизвестной комбинации слотов.
        $skillLevels = $this->playerSkillLevels($player, [Skill::SKILL_HAND_ID]);

        return new FistAttackStrategy(
            hitCalc: $this->hitCalc,
            player: $sheet,
            monster: $monster,
            weaponSkillLevel: $this->skillLevelFrom($skillLevels, Skill::SKILL_HAND_ID),
        );
    }

    /** @param array<int, int> $skillLevels */
    private function skillLevelFrom(array $skillLevels, ?int $skillId): int
    {
        return $skillId === null ? 0 : ($skillLevels[$skillId] ?? 0);
    }

    /** @param array<int, int|null> $skillIds
     * @return array<int, int>
     */
    private function playerSkillLevels(Player $player, array $skillIds): array
    {
        $skillIds = array_values(array_unique(array_filter($skillIds)));

        if ($skillIds === []) {
            return [];
        }

        return PlayerSkill::query()
            ->where('player_id', $player->id)
            ->whereIn('skill_id', $skillIds)
            ->pluck('lvl', 'skill_id')
            ->map(fn ($level): int => (int) $level)
            ->all();
    }
}
