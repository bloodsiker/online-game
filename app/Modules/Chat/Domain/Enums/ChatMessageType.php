<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Enums;

enum ChatMessageType: string
{
    case Message = 'message';
    case Private = 'private';
    case System = 'system';
    case Mention = 'mention';
    case Information = 'information';
    case Quest = 'quest';
    case QuestItem = 'quest_item';
}
