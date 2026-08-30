<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Application\Services\BroadcastMailboxUnreadState;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class ReadLetter
{
    public function __construct(private readonly BroadcastMailboxUnreadState $unreadState) {}

    /**
     * Открыть письмо. Получателю при первом открытии отмечается прочтение;
     * приложенные деньги зачисляются отдельно — кнопкой «Забрать».
     */
    public function execute(User $user, int $letterId): ?PostLetter
    {
        $letter = PostLetter::query()->with(['sender', 'recipient'])->find($letterId);

        if ($letter === null) {
            return null;
        }

        $isRecipient = $letter->recipient_user_id === $user->id && $letter->recipient_deleted_at === null;
        $isSender = $letter->sender_user_id === $user->id && $letter->sender_deleted_at === null;

        if (! $isRecipient && ! $isSender) {
            return null;
        }

        if ($isRecipient && $letter->read_at === null) {
            $letter->read_at = now();
            $letter->save();
            $this->unreadState->sync($user);
        }

        return $letter;
    }
}
