<?php

namespace App\Enums;

enum ItemEffectValueType: string
{
    case FLAT = 'flat';       // +50 hp
    case PERCENT = 'percent'; // +10%
}
