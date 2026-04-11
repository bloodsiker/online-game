<?php

namespace App\Services\Combat;

use App\Enums\CombatClass;

interface FightHitInterface
{
    public function getCritical(): int;
    public function getDodge(): int;
    public function getArmor(): int;
    public function getCombatClass(): CombatClass;
    public function getClassDominance(): float;
}
