<?php

namespace App\Enums;

enum QuestRewardType: string
{
    case EXP = 'exp';
    case MONEY = 'money';
    case ITEM = 'item';
    case LOCATION_ACCESS = 'location_access';
}