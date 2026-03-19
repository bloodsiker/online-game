<?php

namespace App\Enums;

enum ChatMessageType: string
{
    case Message     = 'message';
    case Private     = 'private';
    case System      = 'system';
    case Mention     = 'mention';
    case Information = 'information';
    case Quest       = 'quest';
}