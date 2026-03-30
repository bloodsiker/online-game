<?php

declare(strict_types=1);

namespace App\Enums;

enum PlayerRelationshipType: string
{
    case FRIEND = 'friend';
    case ENEMY  = 'enemy';
    case IGNORE = 'ignore';
}