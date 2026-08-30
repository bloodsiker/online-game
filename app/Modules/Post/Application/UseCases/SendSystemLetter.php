<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Application\DTOs\PostActionResultDTO;
use App\Modules\Post\Application\Services\BroadcastMailboxUnreadState;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class SendSystemLetter
{
    public function __construct(private readonly BroadcastMailboxUnreadState $unreadState) {}

    /**
     * Системное письмо от администрации: одному игроку (по нику) или всем сразу.
     * Приходит без налога и без учёта вместимости ящика; можно вложить деньги
     * и один предмет — игрок заберёт их кнопкой «Забрать».
     */
    public function execute(string $nick, string $subject, string $text, int $money, ?int $shareItemId, int $itemAmount, bool $toAll): PostActionResultDTO
    {
        $subject = trim($subject);
        $text = trim($text);

        if ($subject === '' || $text === '') {
            return new PostActionResultDTO(false, 'Заполните тему и текст письма.');
        }

        if ($money < 0) {
            return new PostActionResultDTO(false, 'Неверная сумма.');
        }

        if ($shareItemId !== null && ! ShareItem::query()->whereKey($shareItemId)->exists()) {
            return new PostActionResultDTO(false, 'Предмет не найден.');
        }

        if ($shareItemId !== null && $itemAmount < 1) {
            return new PostActionResultDTO(false, 'Неверное количество предметов.');
        }

        if ($toAll) {
            $recipientIds = User::query()->pluck('id');
        } else {
            $recipient = User::query()->where('name', trim($nick))->first();

            if ($recipient === null) {
                return new PostActionResultDTO(false, 'Персонаж не найден.');
            }

            $recipientIds = collect([$recipient->id]);
        }

        DB::transaction(function () use ($recipientIds, $subject, $text, $money, $shareItemId, $itemAmount): void {
            foreach ($recipientIds as $recipientId) {
                PostLetter::create([
                    'sender_user_id' => null,
                    'recipient_user_id' => $recipientId,
                    'subject' => $subject,
                    'text' => $text,
                    'money' => $money,
                    'share_item_id' => $shareItemId,
                    'item_amount' => $shareItemId !== null ? $itemAmount : 1,
                ]);
            }
        });

        foreach ($recipientIds as $recipientId) {
            $this->unreadState->markUnread((int) $recipientId);
        }

        return new PostActionResultDTO(
            true,
            $toAll
                ? 'Системное письмо отправлено всем игрокам ('.$recipientIds->count().').'
                : 'Системное письмо отправлено.',
        );
    }
}
