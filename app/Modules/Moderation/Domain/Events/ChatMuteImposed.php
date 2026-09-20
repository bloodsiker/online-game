<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class ChatMuteImposed
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $durationMinutes,
        public ?string $reason,
    ) {}
}
