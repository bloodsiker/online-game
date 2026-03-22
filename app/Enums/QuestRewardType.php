<?php

namespace App\Enums;

enum QuestRewardType: string
{
    case EXP = 'exp';
    case MONEY = 'money';
    case ITEM = 'item';
    case LOCATION_ACCESS = 'location_access';
    case CLAN_POINTS = 'clan_points';
    case REPUTATION_POINTS = 'reputation_points';
}