<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;

/** Срок наказания для системных сообщений чата: «1 д. 2 ч. 30 мин.» на языке читателя. */
final class ChatDurationFormatter
{
    /** @return list<LocalizedChatMessage|string> */
    public static function parts(int $minutes): array
    {
        $duration = [];
        foreach ([
            'days' => intdiv($minutes, 1440),
            'hours' => intdiv($minutes % 1440, 60),
            'minutes' => $minutes % 60,
        ] as $unit => $amount) {
            if ($amount > 0) {
                if ($duration !== []) {
                    $duration[] = ' ';
                }
                $duration[] = new LocalizedChatMessage('chat.duration_'.$unit, ['amount' => $amount]);
            }
        }

        return $duration;
    }
}
