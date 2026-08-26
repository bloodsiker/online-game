<?php

namespace App\Modules\Player\Application\Services\Recovery;

use App\Modules\Player\Application\Services\Recovery\Strategies\FullHealStrategy;
use App\Modules\Player\Application\Services\Recovery\Strategies\RecoveryStrategyInterface;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;

class RecoveryStrategyFactory
{
    public static function make(Structure $structure): RecoveryStrategyInterface
    {
        return match ($structure->type) {
            Structure::TYPE_HEAL => app(FullHealStrategy::class),
        };
    }
}
