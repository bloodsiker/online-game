<?php

namespace App\Services\Combat;

use App\DTO\FightHitDTO;

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

        if ($this->isDodge($attacker, $defender)) {
            return $dto->setDodge(true);
        }

        $damage = mt_rand($min, $max);
        if ($this->isCritical($attacker, $defender)) {
            $dto->setCritical(true);
            $damage *= 2;
        }

        $final = $damage * (100 / (100 + $defender->getArmor()));

        return $dto->setDamage(max(1, round($final)));
    }

    private function isDodge(FightHitInterface $attacker, FightHitInterface $defender): bool
    {
        $chance = max(0, min(100, 5 + ($defender->getDodge() - $attacker->getDodge()) * 0.3));

        return mt_rand(0, 100) < $chance;
    }

    private function isCritical(FightHitInterface $attacker, FightHitInterface $defender): bool
    {
        $chance = max(0, min(100, 5 + ($attacker->getCritical() - $defender->getCritical()) * 0.3));

        return mt_rand(0, 100) < $chance;
    }
}
