<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Domain\DTO;

final readonly class TelegramAccount
{
    public function __construct(
        public int $userId,
        public int $telegramUserId,
        public ?string $username,
    ) {}
}
