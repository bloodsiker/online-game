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
        private readonly TelegramBackpackPreview $preview,
        private readonly TelegramLinkRepository $links,
        private readonly TelegramUpdateRepository $updates,
    ) {}

    /** @return array<string, mixed> */
    public function pageReply(int $userId, int $chatId, BackpackCategory $category = BackpackCategory::MAIN, int $page = 1, ?int $messageId = null): array
    {
        return $this->withLocale($this->read->locale(userId: $userId), function () use ($userId, $chatId, $category, $page, $messageId): array {
            $list = $this->read->backpack(userId: $userId, category: $category, page: $page);
            $lines = ['<b>'.__('telegram.backpack').' — '.$category->label().'</b>'];
            foreach ($list['data'] as $index => $item) {
                $name = mb_substr(preg_replace('/\s+/u', ' ', $item['name']), 0, 65);
                $lines[] = ($index + 1).'. '.htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').' ×'.$item['quantity'];
            }
            if (! $list['data']) {
                $lines[] = __('telegram.backpack_empty');
            }
            $meta = $list['meta'];
            $lines[] = __('telegram.pagination', ['page' => $meta['current_page'], 'last' => $meta['last_page'], 'total' => $meta['total']]);
            $caption = implode("\n", $lines);
            $buttons = [];
            $navigation = [];
            if ($meta['current_page'] > 1) {
                $navigation[] = ['text' => '← '.__('telegram.previous'), 'callback_data' => 'bag:'.$category->value.':'.($meta['current_page'] - 1)];
            }
            $navigation[] = ['text' => '↻ '.$meta['current_page'].'/'.$meta['last_page'], 'callback_data' => 'bag:'.$category->value.':'.$meta['current_page']];
            if ($meta['current_page'] < $meta['last_page']) {
                $navigation[] = ['text' => __('telegram.next').' →', 'callback_data' => 'bag:'.$category->value.':'.($meta['current_page'] + 1)];
            }
            $buttons[] = $navigation;
            $groups = [];
            foreach ($list['categories'] as $group) {
                $groups[] = ['text' => ($group['key'] === $category->value ? '• ' : '').$group['label'].' ('.$group['positions'].')',
                    'callback_data' => 'bag:'.$group['key'].':1'];
            }
            $buttons = [...$buttons, ...array_chunk($groups, 2)];
            $image = $this->preview->create(userId: $userId, telegramId: $chatId, items: $list['data']);
            if ($messageId !== null) {
                return ['method' => 'editMessageMedia', 'chat_id' => $chatId, 'message_id' => $messageId,
                    'media' => ['type' => 'photo', 'media' => $image, 'caption' => $caption, 'parse_mode' => 'HTML'],
                    'reply_markup' => ['inline_keyboard' => $buttons]];
            }

            return ['method' => 'sendPhoto', 'chat_id' => $chatId, 'photo' => $image, 'caption' => $caption,
                'parse_mode' => 'HTML', 'reply_markup' => ['inline_keyboard' => $buttons]];
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
