<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Domain\Contracts;

use App\Modules\Telegram\Domain\DTO\TelegramAccount;

interface TelegramLinkRepository
{
    public function forUser(int $userId): ?TelegramAccount;

    public function forTelegram(int $telegramUserId): ?TelegramAccount;

    public function createToken(int $userId): string;

    public function consumeToken(string $token, int $telegramUserId, ?string $username): TelegramAccount;

    public function unlink(int $userId): void;
}
