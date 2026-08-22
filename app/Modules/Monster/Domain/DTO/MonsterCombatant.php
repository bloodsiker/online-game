<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\DTO;

use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;

/**
 * Обёртка над видовым Monster + активные дебаффы конкретного MonsterOnLocation.
 * Существует потому, что Monster — видовая модель (статы одни на всех особей на
 * карте), а debuff применяется к конкретной боевой копии (MonsterOnLocation) —
 * без этой обёртки дебафф физически негде хранить эффект на бой.
 *
 * Затрагивает только защитные статы (armor/dodge) — см. спеку, стартовый набор
 * заклинаний ограничен дебаффом брони/уворота.
 */
final readonly class MonsterCombatant implements FightHitInterface
{
    /** @param  array<string, float>  $statModifierTotals  Суммарные флэт-дебаффы по стате, напр. ['armor' => -15.0] */
    public function __construct(
        private Monster $monster,
        private array $statModifierTotals = [],
    ) {}

    public function getArmor(): int
    {
        return max(0, $this->monster->getArmor() + (int) round($this->statModifierTotals['armor'] ?? 0));
    }

    public function getDodge(): int
    {
        return max(0, $this->monster->getDodge() + (int) round($this->statModifierTotals['dodge'] ?? 0));
    }

    public function getCritical(): int
    {
        return $this->monster->getCritical();
    }

    public function getCombatClass(): CombatClass
    {
        return $this->monster->getCombatClass();
    }

    public function getClassShare(CombatClass $class): float
    {
        return $this->monster->getClassShare($class);
    }

    public function getCritDamage(): int
    {
        return $this->monster->getCritDamage();
    }

    public function getLevel(): int
    {
        return $this->monster->getLevel();
    }

    public function getBlockChance(): int
    {
        return $this->monster->getBlockChance();
    }

    public function getBlockFlat(): int
    {
        return $this->monster->getBlockFlat();
    }

    public function getBlockPercent(): int
    {
        return $this->monster->getBlockPercent();
    }

    public function getIntelligence(): int
    {
        return $this->monster->getIntelligence();
    }

    public function getMagicResistance(): int
    {
        return $this->monster->getMagicResistance();
    }

    public function getMagicAttack(): int
    {
        return $this->monster->getMagicAttack();
    }
}
