<?php

namespace App\Services\Recovery;

use App\Models\Structure;
use App\Services\Recovery\Strategies\FullHealStrategy;
use App\Services\Recovery\Strategies\RecoveryStrategyInterface;

class RecoveryStrategyFactory
{
    public static function make(Structure $structure): RecoveryStrategyInterface
    {
        return match ($structure->type) {
            Structure::TYPE_HEAL => new FullHealStrategy(),
        };
    }
}
