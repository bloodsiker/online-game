<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Application\Services\BroadcastMailboxUnreadState;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class ReadLetter
{
    public function __construct(private readonly BroadcastMailboxUnreadState $unreadState) {}

    /**
     * Открыть письмо. Получателю при первом открытии отмечается прочтение;
     * приложенные деньги зачисляются отдельно — кнопкой «Забрать».
     */
    public function execute(User $user, int $letterId): ?PostLetter
    {
        return DB::transaction(function () use ($user, $letterId): ?PostLetter {
            User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $letter = PostLetter::query()->with(['sender', 'recipient'])->lockForUpdate()->find($letterId);

            if ($letter === null) {
                return null;
            }

            $isRecipient = (int) $letter->recipient_user_id === (int) $user->id && $letter->recipient_deleted_at === null;
            $isSender = (int) $letter->sender_user_id === (int) $user->id && $letter->sender_deleted_at === null;

            if (! $isRecipient && ! $isSender) {
                return null;
            }

            if ($isRecipient && $letter->read_at === null) {
                $letter->read_at = now();
                $letter->save();
                $this->unreadState->sync($user);
            }

            return $letter;
        });
    }
}
