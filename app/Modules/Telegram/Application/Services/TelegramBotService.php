<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Services;

use App\Modules\Backpack\Domain\Enums\BackpackCategory;
use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\Telegram\Domain\Contracts\TelegramUpdateRepository;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class TelegramBotService
{
    use Localizable;

    public function __construct(
        private readonly TelegramLinkRepository $links,
        private readonly TelegramUpdateRepository $updates,
        private readonly TelegramReadService $read,
        private readonly TelegramBackpackBotService $backpack,
    ) {}

    /** @param array<string, mixed> $update
     * @return array<string, mixed>
     */
    public function handle(array $update): array
    {
        if (isset($update['callback_query'])) {
            return $this->backpack->callback(update: $update);
        }
        $message = $update['message'] ?? [];
        $telegramId = $message['from']['id'] ?? 0;
        // Never send private account data to groups or accept a forwarded sender identity.
        if (($message['chat']['type'] ?? '') !== 'private' || ($message['from']['is_bot'] ?? true)
            || ! is_int($telegramId) || $telegramId <= 0 || $telegramId > 4503599627370495
            || ($message['chat']['id'] ?? null) !== $telegramId || ! is_string($message['text'] ?? null)) {
            return ['ok' => true];
        }

        return $this->updates->once(updateId: $update['update_id'], reply: function () use ($message, $telegramId): array {
            $account = $this->links->forTelegram(telegramUserId: $telegramId);
            $locale = $account ? $this->read->locale(userId: $account->userId)
                : (($message['from']['language_code'] ?? '') === 'uk' ? 'uk' : 'ru');

            return $this->withLocale($locale, function () use ($message, $telegramId, $account): array {
                $parts = preg_split('/\s+/', trim($message['text']), 3);
                $command = strtolower(explode('@', $parts[0])[0]);
                if ($command === '/start' && isset($parts[1])) {
                    try {
                        $username = $message['from']['username'] ?? null;
                        $account = $this->links->consumeToken(token: $parts[1], telegramUserId: $telegramId,
                            username: is_string($username) && preg_match('/^[A-Za-z0-9_]{1,32}$/D', $username) ? $username : null);
                        $text = $this->read->character(userId: $account->userId);

                        return $this->withLocale($this->read->locale(userId: $account->userId),
                            fn (): array => $this->reply(chatId: $telegramId, text: __('telegram.linked_reply', ['name' => $this->escape($text['nickname'])])));
                    } catch (ValidationException $exception) {
                        return $this->reply(chatId: $telegramId, text: $this->escape(collect($exception->errors())->flatten()->first()), menu: false);
                    }
                }
                if (! $account) {
                    return $this->reply(chatId: $telegramId, text: __('telegram.not_linked'), menu: false);
                }
                // Check eligibility for every command, including help after an account changes status.
                try {
                    $character = $this->read->character(userId: $account->userId);
                } catch (HttpException $exception) {
                    if ($exception->getStatusCode() !== 403) {
                        throw $exception;
                    }

                    return $this->reply(chatId: $telegramId, text: __('telegram.account_unavailable'), menu: false);
                }
                if (in_array($message['text'], [__('telegram.character', locale: 'ru'), __('telegram.character', locale: 'uk')], true)) {
                    $command = '/character';
                } elseif (in_array($message['text'], [__('telegram.auction', locale: 'ru'), __('telegram.auction', locale: 'uk')], true)) {
                    $command = '/auction';
                    unset($parts[1]);
                } elseif (in_array($message['text'], [__('telegram.exchange', locale: 'ru'), __('telegram.exchange', locale: 'uk')], true)) {
                    $command = '/exchange';
                    unset($parts[1]);
                } elseif (in_array($message['text'], [__('telegram.backpack', locale: 'ru'), __('telegram.backpack', locale: 'uk')], true)) {
                    $command = '/backpack';
                    unset($parts[1], $parts[2]);
                }
                if ($command === '/backpack') {
                    $category = BackpackCategory::tryFrom($parts[1] ?? 'main');
                    $page = $parts[2] ?? '1';
                    if (! $category || ! ctype_digit($page) || (int) $page < 1 || (int) $page > 100000) {
                        return $this->reply(chatId: $telegramId, text: __('telegram.invalid_backpack_selection'));
                    }

                    return $this->backpack->pageReply(userId: $account->userId, chatId: $telegramId, category: $category, page: (int) $page);
                }
                if ($command === '/character') {
                    return $this->reply(chatId: $telegramId, text: __('telegram.character_reply', [
                        'name' => $this->escape($character['nickname']), 'level' => $character['level'],
                        'money' => $this->number($character['money']), 'diamonds' => $this->number($character['diamonds']),
                    ]));
                }
                if (in_array($command, ['/auction', '/exchange'], true)) {
                    $page = $parts[1] ?? '1';
                    if (! ctype_digit($page) || (int) $page < 1 || (int) $page > 100000) {
                        return $this->reply(chatId: $telegramId, text: __('telegram.invalid_page'));
                    }
                    $type = substr($command, 1);
                    $list = $this->read->listings(userId: $account->userId, type: $type, page: (int) $page);
                    $lines = ['<b>'.__('telegram.'.$type).'</b>'];
                    foreach ($list['data'] as $row) {
                        $lines[] = '#'.$row['id'].' '.$this->escape(mb_strimwidth($row['name'], 0, 120, '…'))
                            .' ×'.$row['quantity'].' — '.$this->number($row['total_price']).' '.__('telegram.coins')
                            .' ('.__('telegram.unit_price').': '.$this->number($row['unit_price']).')';
                    }
                    if (! $list['data']) {
                        $lines[] = __('telegram.empty');
                    }
                    $lines[] = __('telegram.pagination', ['page' => $list['meta']['current_page'], 'last' => $list['meta']['last_page'], 'total' => $list['meta']['total']]);
                    if ($list['meta']['current_page'] < $list['meta']['last_page']) {
                        $lines[] = __('telegram.next_page', ['command' => $command.' '.($list['meta']['current_page'] + 1)]);
                    }

                    return $this->reply(chatId: $telegramId, text: implode("\n", $lines));
                }

                return $this->reply(chatId: $telegramId, text: __('telegram.help'));
            });
        });
    }

    /** @return array<string, mixed> */
    private function reply(int $chatId, string $text, bool $menu = true): array
    {
        $result = ['method' => 'sendMessage', 'chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML'];
        if ($menu) {
            $result['reply_markup'] = ['keyboard' => [[__('telegram.character'), __('telegram.backpack')], [__('telegram.auction'), __('telegram.exchange')]], 'resize_keyboard' => true];
        } else {
            $result['reply_markup'] = ['remove_keyboard' => true];
        }

        return $result;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function number(int|float|string $value): string
    {
        $value = (float) $value;

        return number_format($value, $value == (int) $value ? 0 : 2, '.', ' ');
    }
}
