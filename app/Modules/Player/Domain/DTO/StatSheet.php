<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\DTO;

use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;

class StatSheet implements FightHitInterface
{
    public int $strength = 0;

    public int $intuition = 0;

    public int $agility = 0;

    /**
     * «Родные» первичные статы игрока без учёта бонусов экипировки/камней/рун —
     * используются только для определения боевого класса (см. combatClassShares()),
     * чтобы временный +agility с руны не превращал крит-билд в «Гибрид».
     */
    public int $baseStrength = 0;

    public int $baseAgility = 0;

    public int $baseIntuition = 0;

    public int $wisdom = 0;

    public int $intelligence = 0;

    /** Отвечает только за HP: не входит в определение боевого класса (танк/уворот/крит) */
    public int $endurance = 0;

    public int $dodge = 0;

    public int $critical = 0;

    public int $armor = 0;

    public int $hpMax = 0;

    public int $mpMax = 0;

    public int $leftMinDmg = 0;

    public int $leftMaxDmg = 0;

    public int $rightMinDmg = 0;

    public int $rightMaxDmg = 0;

    public int $magicAttack = 0;

    /** Множитель критического урона, % (150 = ×1.5) */
    public int $critDamage = 150;

    public int $level = 1;

    public int $freeStats = 0;

    /** Блок щитом: шанс срабатывания, % */
    public int $blockChance = 0;

    /** Блок щитом: фиксированное поглощение+отражение урона при срабатывании */
    public int $blockFlat = 0;

    /** Блок щитом: процент урона, поглощаемый+отражаемый при срабатывании */
    public int $blockPercent = 0;

    public CombatClass $combatClass = CombatClass::TANK;

    /** @var StatModifier[] */
    public array $modifiers = [];

    // FightHitInterface
    public function getArmor(): int
    {
        return $this->armor;
    }

    public function getDodge(): int
    {
        return $this->dodge;
    }

    public function getCritical(): int
    {
        return $this->critical;
    }

    public function getCombatClass(): CombatClass
    {
        return $this->combatClass;
    }

    public function getDisplayCombatClassLabel(): string
    {
        $shares = $this->combatClassShares();
        $maxShare = max($shares);
        $minShare = min($shares);

        if (($maxShare - $minShare) <= 0.10) {
            return 'Универсал';
        }

        arsort($shares);
        $classes = array_keys($shares);
        $primary = CombatClass::from($classes[0]);
        $secondary = CombatClass::from($classes[1]);

        if (($shares[$classes[0]] - $shares[$classes[1]]) <= 0.15) {
            return 'Гибрид: '.$primary->getLabel().'/'.$secondary->getLabel();
        }

        return $primary->getLabel();
    }

    public function getDisplayCombatClassColor(): string
    {
        $shares = $this->combatClassShares();
        $maxShare = max($shares);
        $minShare = min($shares);

        if (($maxShare - $minShare) <= 0.10) {
            return '#6b5a2a';
        }

        arsort($shares);
        $classes = array_keys($shares);

        if (($shares[$classes[0]] - $shares[$classes[1]]) <= 0.15) {
            return '#6a4a7a';
        }

        return match (CombatClass::from($classes[0])) {
            CombatClass::TANK => '#3a5a8a',
            CombatClass::DODGE => '#3a7a4a',
            CombatClass::CRIT => '#8a3a3a',
        };
    }

    public function getClassShare(CombatClass $class): float
    {
        return $this->combatClassShares()[$class->value];
    }

    /** @return array<string, float> */
    private function combatClassShares(): array
    {
        $str = (float) $this->baseStrength;
        $agil = (float) $this->baseAgility;
        $int = (float) $this->baseIntuition;
        $total = max(1.0, $str + $agil + $int);

        return [
            CombatClass::TANK->value => $str / $total,
            CombatClass::DODGE->value => $agil / $total,
            CombatClass::CRIT->value => $int / $total,
        ];
    }

    public function getCritDamage(): int
    {
        return $this->critDamage;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getBlockChance(): int
    {
        return $this->blockChance;
    }

    public function getBlockFlat(): int
    {
        return $this->blockFlat;
    }

    public function getBlockPercent(): int
    {
        return $this->blockPercent;
    }

    // Stat getters (used in views and combat strategies)
    public function getStrength(): int
    {
        return $this->strength;
    }

    public function getInt(): int
    {
        return $this->intuition;
    }

    public function getAgility(): int
    {
        return $this->agility;
    }

    public function getMud(): int
    {
        return $this->wisdom;
    }

    public function getIntelligence(): int
    {
        return $this->intelligence;
    }

    public function getEndurance(): int
    {
        return $this->endurance;
    }

    public function getHpMax(): int
    {
        return $this->hpMax;
    }

    public function getMpMax(): int
    {
        return $this->mpMax;
    }

    public function getFreeStats(): int
    {
        return $this->freeStats;
    }

    public function getLeftHandMinDmg(): int
    {
        return $this->leftMinDmg;
    }

    public function getLeftHandMaxDmg(): int
    {
        return $this->leftMaxDmg;
    }

    public function getRightHandMinDmg(): int
    {
        return $this->rightMinDmg;
    }

    public function getRightHandMaxDmg(): int
    {
        return $this->rightMaxDmg;
    }

    /**
     * Returns all modifiers for a stat — for tooltip breakdown in UI.
     *
     * @return StatModifier[]
     */
    public function getBreakdown(string $stat): array
    {
        return array_values(array_filter($this->modifiers, fn ($m) => $m->stat === $stat));
    }
}
