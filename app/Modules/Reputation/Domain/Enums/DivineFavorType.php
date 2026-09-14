<?php

declare(strict_types=1);

namespace App\Modules\Reputation\Domain\Enums;

enum DivineFavorType: string
{
    case HEAL = 'heal';
    case POISON = 'poison';
    case ATTACK_BUFF = 'attack_buff';
}
