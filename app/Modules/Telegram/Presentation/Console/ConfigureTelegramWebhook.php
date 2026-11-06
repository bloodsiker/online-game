<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Console;

use App\Modules\Telegram\Application\Services\TelegramLinkService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class ConfigureTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {--url= : Публичный HTTPS URL приложения, по умолчанию APP_URL}';

    protected $description = 'Зарегистрировать защищённый webhook Telegram-бота';

    public function handle(TelegramLinkService $links): int
    {
        $base = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        if (! $links->available() || ! preg_match('/^[A-Za-z0-9_-]{32,256}$/D', (string) config('telegram.webhook_secret'))) {
            $this->error('Укажите TELEGRAM_BOT_TOKEN, TELEGRAM_BOT_USERNAME и TELEGRAM_WEBHOOK_SECRET (32–256 символов A-Z, a-z, 0-9, _ и -).');

            return self::FAILURE;
        }
        if (! filter_var($base, FILTER_VALIDATE_URL) || parse_url($base, PHP_URL_SCHEME) !== 'https'
            || parse_url($base, PHP_URL_USER) !== null || parse_url($base, PHP_URL_QUERY) !== null || parse_url($base, PHP_URL_FRAGMENT) !== null) {
            $this->error('Нужен публичный HTTPS URL приложения без параметров и учётных данных.');

            return self::FAILURE;
        }
        try {
            $response = Http::connectTimeout(5)->timeout(15)->post('https://api.telegram.org/bot'.config('telegram.bot_token').'/setWebhook', [
                'url' => $base.'/telegram/webhook', 'secret_token' => config('telegram.webhook_secret'),
                'allowed_updates' => ['message'],
            ]);
        } catch (ConnectionException) {
            // Do not print HTTP exceptions: their URL contains the bot token.
            $this->error('Не удалось подключиться к Telegram. Проверьте сеть и повторите команду.');

            return self::FAILURE;
        }
        if (! $response->successful() || $response->json('ok') !== true) {
            $this->error('Telegram не принял webhook. Проверьте токен, HTTPS и доступность приложения.');

            return self::FAILURE;
        }
        $this->info('Webhook зарегистрирован: '.$base.'/telegram/webhook');

        return self::SUCCESS;
    }
}
