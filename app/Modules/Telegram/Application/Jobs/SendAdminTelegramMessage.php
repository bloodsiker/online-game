<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

/**
 * Служебное сообщение админу в Telegram (игровой бот → TELEGRAM_ADMIN_CHAT_ID).
 */
final class SendAdminTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public function __construct(public readonly string $text)
    {
        $this->afterCommit();
    }

    public static function enabled(): bool
    {
        return config('telegram.bot_token') !== '' && config('telegram.admin_chat_id') !== '';
    }

    public function handle(): void
    {
        if (! self::enabled()) {
            return;
        }

        $response = Http::connectTimeout(3)->timeout(5)->post(
            url: 'https://api.telegram.org/bot'.config('telegram.bot_token').'/sendMessage',
            data: [
                'chat_id' => config('telegram.admin_chat_id'),
                'text' => $this->text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ],
        );

        // Не бросаем RequestException: в его тексте был бы URL с токеном бота.
        if ($response->failed()) {
            $this->release(30);
        }
    }
}
