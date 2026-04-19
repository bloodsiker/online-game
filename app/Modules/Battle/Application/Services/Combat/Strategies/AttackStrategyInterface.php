<?php

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

interface AttackStrategyInterface
{
    public function getHits(): array;
}
