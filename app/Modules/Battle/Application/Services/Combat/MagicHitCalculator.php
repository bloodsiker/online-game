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
 * критует вовсе), но, как и физический крит, упирается в мягкий потолок —
 * PlayerStatFormulas::effectiveMagicCriticalChance() асимптотически
 * ограничивает шанс 75%, иначе сырое значение с нескольких артефактов легко
 * уходит за 100% (гарантированный крит на каждом касте); множитель урона
 * крита — getMagicCritDamage(), отдельная от физического крита стата,
 * растущая исключительно с экипировки (см. ShareItemStatType::MAGIC_CRIT_DAMAGE),
 * без вклада от Интуиции.
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
        int $magicSkillLevel = 0,
    ): FightHitDTO {
        $dto = new FightHitDTO;

        $rolled = random_int(min($minDamage, $maxDamage), max($minDamage, $maxDamage));
        $rawDamage = $rolled + (int) round($this->magicPower($attacker, $magicSkillLevel) * $powerCoefficient);

        $magicCritChance = PlayerStatFormulas::effectiveMagicCriticalChance($attacker->getMagicCriticalChance());
        $isCrit = $magicCritChance > 0
            && random_int(1, 10000) <= (int) round($magicCritChance * 100);
        if ($isCrit) {
            $dto->setCritical(true);
            $rawDamage = (int) round($rawDamage * PlayerStatFormulas::effectiveCritDamage($attacker->getMagicCritDamage()) / 100);
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
        int $magicSkillLevel = 0,
    ): int {
        $rolled = random_int(min($minHeal, $maxHeal), max($minHeal, $maxHeal));

        return $rolled + (int) round($this->magicPower($caster, $magicSkillLevel) * $powerCoefficient);
    }

    /**
     * Навык «Колдовство» усиливает силу заклинаний той же кривой, что оружейный
     * навык — физический урон (HitCalculator::weaponSkillMultiplier), ради
     * симметрии магии и оружия. Интеллект капается мягким потолком
     * (PlayerStatFormulas::cappedIntelligence) — без него чистый стат-стак
     * давал бы неограниченный урон без оглядки на magic-снаряжение; magic_attack
     * с предметов остаётся без ограничения, как урон оружия у физики.
     */
    private function magicPower(FightHitInterface $caster, int $magicSkillLevel = 0): float
    {
        $base = PlayerStatFormulas::cappedIntelligence((float) $caster->getIntelligence(), $caster->getLevel())
            + $caster->getMagicAttack();
        $base *= 1 + $caster->getMagicDamagePercent() / 100;

        return $base * HitCalculator::weaponSkillMultiplier($magicSkillLevel);
    }

    private function levelScale(FightHitInterface $attacker, FightHitInterface $defender): float
    {
        $avgLevel = ($attacker->getLevel() + $defender->getLevel()) / 2;

        return max(1.0, $avgLevel / self::REFERENCE_LEVEL);
    }
}
