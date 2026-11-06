<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Infrastructure;

use App\Modules\Telegram\Domain\Contracts\TelegramCallbackResponder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class TelegramCallbackAcknowledger implements TelegramCallbackResponder
{
    public function acknowledge(string $callbackId): void
    {
        try {
            Http::connectTimeout(1)->timeout(2)->post('https://api.telegram.org/bot'.config('telegram.bot_token').'/answerCallbackQuery', [
                'callback_query_id' => $callbackId,
            ]);
        } catch (ConnectionException) {
            // Still return the updated page. Never log exceptions containing the bot token in their URL.
        }
    }
}
