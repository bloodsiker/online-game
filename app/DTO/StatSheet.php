<?php

namespace App\DTO;

use App\Services\Combat\FightHitInterface;

class StatSheet implements FightHitInterface
{
    public int $strength     = 0;
    public int $int          = 0;
    public int $agility      = 0;
    public int $mud          = 0;
    public int $intelligence = 0;
    public int $dodge        = 0;
    public int $critical     = 0;
    public int $armor        = 0;
    public int $hpMax        = 0;
    public int $mpMax        = 0;
    public int $leftMinDmg   = 0;
    public int $leftMaxDmg   = 0;
    public int $rightMinDmg  = 0;
    public int $rightMaxDmg  = 0;
    public int $freeStats    = 0;

    /** @var StatModifier[] */
    public array $modifiers = [];

    // FightHitInterface
    public function getArmor(): int    { return $this->armor; }
    public function getDodge(): int    { return $this->dodge; }
    public function getCritical(): int { return $this->critical; }

    // Stat getters (used in views and combat strategies)
    public function getStrength(): int     { return $this->strength; }
    public function getInt(): int          { return $this->int; }
    public function getAgility(): int      { return $this->agility; }
    public function getMud(): int          { return $this->mud; }
    public function getIntelligence(): int { return $this->intelligence; }
    public function getHpMax(): int        { return $this->hpMax; }
    public function getMpMax(): int        { return $this->mpMax; }
    public function getFreeStats(): int    { return $this->freeStats; }
    public function getLeftHandMinDmg(): int  { return $this->leftMinDmg; }
    public function getLeftHandMaxDmg(): int  { return $this->leftMaxDmg; }
    public function getRightHandMinDmg(): int { return $this->rightMinDmg; }
    public function getRightHandMaxDmg(): int { return $this->rightMaxDmg; }

    /**
     * Returns all modifiers for a stat — for tooltip breakdown in UI.
     * @return StatModifier[]
     */
    public function getBreakdown(string $stat): array
    {
        return array_values(array_filter($this->modifiers, fn($m) => $m->stat === $stat));
    }
}