<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ItemEffectValueType: string
{
    case FLAT = 'flat';
    case PERCENT = 'percent';
}
