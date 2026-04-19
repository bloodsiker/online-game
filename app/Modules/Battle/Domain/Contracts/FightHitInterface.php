<?php

namespace App\Modules\Battle\Domain\Contracts;

use App\Modules\Battle\Domain\Enums\CombatClass;

interface FightHitInterface
{
    public function getCritical(): int;

    public function getDodge(): int;

    public function getArmor(): int;

    public function getCombatClass(): CombatClass;

    public function getClassDominance(): float;
}
