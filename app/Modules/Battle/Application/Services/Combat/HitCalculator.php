<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\DTO\FightHitDTO;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;

readonly class HitCalculator
{
    public function playerHit(FightHitInterface $attacker, FightHitInterface $defender, $min, $max): FightHitDTO
    {
        return $this->calculateHit($attacker, $defender, $min, $max);
    }

    public function monsterHit(FightHitInterface $attacker, FightHitInterface $defender, $min, $max): FightHitDTO
    {
        return $this->calculateHit($attacker, $defender, $min, $max);
    }

    private function calculateHit(FightHitInterface $attacker, FightHitInterface $defender, $min, $max): FightHitDTO
    {
        $dto = new FightHitDTO;

        $attackerClass = $attacker->getCombatClass();
        $defenderClass = $defender->getCombatClass();

        if ($this->isDodge($attacker, $defender, $attackerClass, $defenderClass)) {
            return $dto->setDodge(true);
        }

        $damage = mt_rand($min, $max);
        $isCrit = $this->isCritical($attacker, $defender, $attackerClass, $defenderClass);

        if ($isCrit) {
            $dto->setCritical(true);
            $damage *= 2;
        }

        // Крит > Танк: пробитие брони до 50%, масштабируется по доминированию атакующего
        // Чистый CRIT (dominance≈0.9) → -45% брони; гибрид (dominance≈0.35) → -17% брони
        $effectiveArmor = $defender->getArmor();
        if ($isCrit
            && $attackerClass === CombatClass::CRIT
            && $defenderClass === CombatClass::TANK
        ) {
            $pierce = 0.5 * $attacker->getClassDominance();
            $effectiveArmor = (int) ($effectiveArmor * (1 - $pierce));
        }

        $final = $damage * (100 / (100 + $effectiveArmor));

        return $dto->setDamage(max(1, (int) round($final)));
    }

    private function isDodge(
        FightHitInterface $attacker,
        FightHitInterface $defender,
        CombatClass $attackerClass,
        CombatClass $defenderClass,
    ): bool {
        $chance = max(0, min(100, 5 + ($defender->getDodge() - $attacker->getDodge()) * 0.3));

        // Танк > Уворот: снижение шанса уворота до 40%, масштабируется по доминированию атакующего
        // Чистый TANK (dominance≈0.9) → -36%; гибрид (dominance≈0.35) → -14%
        if ($attackerClass === CombatClass::TANK && $defenderClass === CombatClass::DODGE) {
            $reduction = 0.4 * $attacker->getClassDominance();
            $chance *= (1 - $reduction);
        }

        return mt_rand(0, 100) < $chance;
    }

    private function isCritical(
        FightHitInterface $attacker,
        FightHitInterface $defender,
        CombatClass $attackerClass,
        CombatClass $defenderClass,
    ): bool {
        $chance = max(0, min(100, 5 + ($attacker->getCritical() - $defender->getCritical()) * 0.3));

        // Уворот > Крит: снижение шанса крита до 40%, масштабируется по доминированию защитника
        // Чистый DODGE защитник (dominance≈0.9) → -36%; гибрид (dominance≈0.35) → -14%
        if ($attackerClass === CombatClass::CRIT && $defenderClass === CombatClass::DODGE) {
            $reduction = 0.4 * $defender->getClassDominance();
            $chance *= (1 - $reduction);
        }

        return mt_rand(0, 100) < $chance;
    }
}
