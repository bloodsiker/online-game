<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Enums;

enum LocationItemInteractionType: string
{
    case PICKUP = 'pickup';
    case OPEN_HERE = 'open_here';
}
