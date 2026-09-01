<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;

/**
 * Отдельный урон-калькулятор для магии — НЕ переиспользует HitCalculator::hit().
 * Магия не уворачивается и не блокируется щитом (см. спеку
 * docs/superpowers/specs/2026-08-22-magic-combat-system-design.md, правило 1) —
 * единственная защита цели — magic_resistance. Крит магии — отдельное от
 * физического правило: не контестуется со статой защитника (в отличие от
 * HitCalculator::isCritical()), а даётся исключительно экипировкой атакующего
 * (getMagicCriticalChance(), база 0 — по умолчанию маг без такого оружия не
 * критует вовсе); множитель урона крита переиспользует общую формулу
 * PlayerStatFormulas::effectiveCritDamage(), как и физический крит.
 */
readonly class MagicHitCalculator
{
    /**
     * Уровень, на котором калибруется знаменатель сопротивления — то же значение
     * и та же логика масштабирования, что у HitCalculator::ARMOR_CONSTANT.
     */
    private const REFERENCE_LEVEL = 12.0;

    /** Знаменатель формулы резиста на референсном уровне: damageMultiplier = A/(A+resist) */
    private const MAGIC_RESIST_CONSTANT = 220.0;

    public function hit(
        FightHitInterface $attacker,
        FightHitInterface $defender,
        int $minDamage,
        int $maxDamage,
        float $powerCoefficient,
    ): FightHitDTO {
        $dto = new FightHitDTO;

        $rolled = random_int(min($minDamage, $maxDamage), max($minDamage, $maxDamage));
        $rawDamage = $rolled + (int) round($this->magicPower($attacker) * $powerCoefficient);

        $isCrit = $attacker->getMagicCriticalChance() > 0
            && random_int(1, 100) <= $attacker->getMagicCriticalChance();
        if ($isCrit) {
            $dto->setCritical(true);
            $rawDamage = (int) round($rawDamage * PlayerStatFormulas::effectiveCritDamage($attacker->getCritDamage()) / 100);
        }

        $resistConstant = self::MAGIC_RESIST_CONSTANT * $this->levelScale($attacker, $defender);
        $damageMultiplier = $resistConstant / ($resistConstant + $defender->getMagicResistance());

        $final = max(1, (int) round($rawDamage * $damageMultiplier));

        return $dto->setDamage($final);
    }

    /** Лечение не резистится целью — сила от кастера, как и урон, но без шага митигации. */
    public function heal(
        FightHitInterface $caster,
        int $minHeal,
        int $maxHeal,
        float $powerCoefficient,
    ): int {
        $rolled = random_int(min($minHeal, $maxHeal), max($minHeal, $maxHeal));

        return $rolled + (int) round($this->magicPower($caster) * $powerCoefficient);
    }

    private function magicPower(FightHitInterface $caster): float
    {
        return (float) ($caster->getIntelligence() + $caster->getMagicAttack());
    }

    private function levelScale(FightHitInterface $attacker, FightHitInterface $defender): float
    {
        $avgLevel = ($attacker->getLevel() + $defender->getLevel()) / 2;

        return max(1.0, $avgLevel / self::REFERENCE_LEVEL);
    }
}
