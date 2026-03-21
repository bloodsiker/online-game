<?php

namespace App\DTO;

readonly class StatModifier
{
    public function __construct(
        public string $stat,      // 'strength', 'armor', 'hp_max', 'left_min_dmg', etc.
        public float  $value,
        public bool   $isPercent,
        public string $source,    // 'equipment:Меч воина', 'passive:Закалка', 'buff:Ярость'
    ) {}
}