<?php

namespace App\Services\Combat;

use App\DTO\FightHitDTO;
use App\Enums\CombatClass;

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
        $dto = new FightHitDTO();

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

        // Крит > Танк: критический удар пробивает 50% брони Танка
        $effectiveArmor = $defender->getArmor();
        if ($isCrit
            && $attackerClass === CombatClass::CRIT
            && $defenderClass === CombatClass::TANK
        ) {
            $effectiveArmor = (int) ($effectiveArmor * 0.5);
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

        // Танк > Уворот: масса и напор танка снижают шанс уворота на 40%
        if ($attackerClass === CombatClass::TANK && $defenderClass === CombatClass::DODGE) {
            $chance *= 0.6;
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

        // Уворот > Крит: уклонист сбивает прицел — шанс крита снижен на 40%
        if ($attackerClass === CombatClass::CRIT && $defenderClass === CombatClass::DODGE) {
            $chance *= 0.6;
        }

        return mt_rand(0, 100) < $chance;
    }
}