<?php

namespace App\Services\Combat;

interface FightHitInterface
{
    public function getCritical(): int;
    public function getDodge(): int;
    public function getArmor(): int;
}
