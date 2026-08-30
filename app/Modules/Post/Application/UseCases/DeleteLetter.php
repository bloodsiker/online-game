<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Application\Services\BroadcastMailboxUnreadState;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class DeleteLetter
{
    public function __construct(private readonly BroadcastMailboxUnreadState $unreadState) {}

    /**
     * Удаляет письмо у своей стороны (у отправителя и получателя корзины раздельные).
     * Непрочитанное письмо удаляется вместе с приложенными деньгами — как на проде.
     */
    public function execute(User $user, int $letterId): bool
    {
        $letter = PostLetter::query()->find($letterId);

        if ($letter === null) {
            return false;
        }

        if ($letter->recipient_user_id === $user->id && $letter->recipient_deleted_at === null) {
            $wasUnread = $letter->read_at === null;
            $letter->recipient_deleted_at = now();
            $letter->save();

            if ($wasUnread) {
                $this->unreadState->sync($user);
            }

            return true;
        }

        if ($letter->sender_user_id === $user->id && $letter->sender_deleted_at === null) {
            $letter->sender_deleted_at = now();
            $letter->save();

            return true;
        }

        return false;
    }
}
