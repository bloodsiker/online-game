<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Backpack\Domain\Enums\BackpackCategory;
use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\Telegram\Domain\Contracts\TelegramUpdateRepository;
use Illuminate\Support\Traits\Localizable;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class TelegramBackpackBotService
{
    use Localizable;

    public function __construct(
        private readonly TelegramReadService $read,
        private readonly TelegramLinkRepository $links,
        private readonly TelegramUpdateRepository $updates,
    ) {}

    /** @return array<string, mixed> */
    public function pageReply(int $userId, int $chatId, BackpackCategory $category = BackpackCategory::MAIN, int $page = 1, ?int $messageId = null): array
    {
        return $this->withLocale($this->read->locale(userId: $userId), function () use ($userId, $chatId, $category, $page, $messageId): array {
            $this->read->character(userId: $userId);
            $keyboard = ['inline_keyboard' => [[['text' => __('telegram.mini_app_open'), 'web_app' => [
                'url' => secure_url(route('telegram.backpack.app', ['category' => $category->value, 'page' => $page, 'lang' => app()->getLocale()], absolute: false)),
            ]]]]];
            $text = __('telegram.mini_app_intro');
            if ($messageId !== null) {
                // Upgrade buttons on old photo messages without generating another collage.
                return ['method' => 'editMessageCaption', 'chat_id' => $chatId, 'message_id' => $messageId,
                    'caption' => $text, 'reply_markup' => $keyboard];
            }

            return ['method' => 'sendMessage', 'chat_id' => $chatId, 'text' => $text, 'reply_markup' => $keyboard];
        });
    }

    /** @param array<string, mixed> $update
     * @return array<string, mixed>
     */
    public function callback(array $update): array
    {
        $callback = $update['callback_query'];
        $telegramId = $callback['from']['id'] ?? 0;
        $message = $callback['message'] ?? [];
        if (! is_int($telegramId) || $telegramId <= 0 || $telegramId > 4503599627370495
            || ($callback['from']['is_bot'] ?? true) || ($message['chat']['type'] ?? '') !== 'private'
            || ($message['chat']['id'] ?? null) !== $telegramId || ! is_int($message['message_id'] ?? null)
            || $message['message_id'] < 1 || ! is_string($callback['id'] ?? null)) {
            return ['ok' => true];
        }

        return $this->updates->once(updateId: $update['update_id'], reply: function () use ($callback, $telegramId, $message): array {
            $account = $this->links->forTelegram(telegramUserId: $telegramId);
            $locale = $account ? $this->read->locale(userId: $account->userId)
                : (($callback['from']['language_code'] ?? '') === 'uk' ? 'uk' : 'ru');

            return $this->withLocale($locale, function () use ($callback, $telegramId, $message, $account): array {
                if (! $account) {
                    return $this->alert(callbackId: $callback['id'], text: __('telegram.not_linked'));
                }
                if (! is_string($callback['data'] ?? null)
                    || ! preg_match('/^bag:(main|misc|key|quest|artifact|gift):([1-9][0-9]{0,5})$/D', $callback['data'], $parts)
                    || (int) $parts[2] > 100000) {
                    return $this->alert(callbackId: $callback['id'], text: __('telegram.invalid_backpack_selection'));
                }
                try {
                    return $this->pageReply(userId: $account->userId, chatId: $telegramId,
                        category: BackpackCategory::from($parts[1]), page: (int) $parts[2], messageId: $message['message_id']);
                } catch (HttpException $exception) {
                    if ($exception->getStatusCode() !== 403) {
                        throw $exception;
                    }

                    return $this->alert(callbackId: $callback['id'], text: __('telegram.account_unavailable'));
                }
            });
        });
    }

    /** @return array<string, mixed> */
    private function alert(string $callbackId, string $text): array
    {
        return ['method' => 'answerCallbackQuery', 'callback_query_id' => $callbackId,
            'text' => mb_substr($text, 0, 190), 'show_alert' => true];
    }
}
