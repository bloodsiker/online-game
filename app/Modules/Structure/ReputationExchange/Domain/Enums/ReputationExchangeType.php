<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Domain\Enums;

enum ReputationExchangeType: string
{
    case LINEAR = 'linear';
    case GAMBLE = 'gamble';
}
