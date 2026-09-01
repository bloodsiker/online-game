<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;

final class StubCombatant implements FightHitInterface
{
    public function __construct(
        private int $level = 12,
        private int $intelligence = 0,
        private int $magicAttack = 0,
        private int $magicResistance = 0,
        private int $magicCriticalChance = 0,
    ) {}

    public function getCritical(): int
    {
        return 0;
    }

    public function getDodge(): int
    {
        return 0;
    }

    public function getArmor(): int
    {
        return 0;
    }

    public function getCombatClass(): CombatClass
    {
        return CombatClass::TANK;
    }

    public function getClassShare(CombatClass $class): float
    {
        return 1 / 3;
    }

    public function getCritDamage(): int
    {
        return 175;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getBlockChance(): int
    {
        return 0;
    }

    public function getBlockFlat(): int
    {
        return 0;
    }

    public function getBlockPercent(): int
    {
        return 0;
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function getMagicResistance(): int
    {
        return $this->magicResistance;
    }

    public function getMagicAttack(): int
    {
        return $this->magicAttack;
    }

    public function getMagicCriticalChance(): int
    {
        return $this->magicCriticalChance;
    }
}
