<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Domain\Exceptions;

use App\Modules\Moderation\Domain\Models\UserCommunicationMute;
use RuntimeException;

class UserMutedException extends RuntimeException
{
    public function __construct(public readonly UserCommunicationMute $mute)
    {
        $message = sprintf(
            'Вам запрещено писать %s до %s.',
            $mute->scope->restrictionLabel(),
            $mute->expires_at->format('d.m.Y H:i'),
        );

        if ($mute->reason !== null && $mute->reason !== '') {
            $message .= ' Причина: '.$mute->reason;
        }

        parent::__construct($message);
    }
}
