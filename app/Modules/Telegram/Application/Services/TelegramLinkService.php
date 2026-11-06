<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;

final class TelegramLinkService
{
    public function __construct(private readonly TelegramLinkRepository $links) {}

    public function available(): bool
    {
        return preg_match('/^[A-Za-z0-9_]{5,32}$/D', (string) config('telegram.bot_username')) === 1
            && filled(config('telegram.bot_token')) && strlen((string) config('telegram.webhook_secret')) >= 32;
    }

    public function createLink(int $userId): string
    {
        abort_unless($this->available(), 503, __('telegram.unavailable'));

        return 'https://t.me/'.config('telegram.bot_username').'?start='.$this->links->createToken(userId: $userId);
    }
}
