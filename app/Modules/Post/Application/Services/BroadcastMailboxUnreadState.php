<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\Services;

use App\Modules\Post\Application\UseCases\GetMailbox;
use App\Modules\Post\Domain\Events\MailboxUnreadStateUpdated;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class BroadcastMailboxUnreadState
{
    public function __construct(private GetMailbox $mailbox) {}

    public function markUnread(User|int $user): void
    {
        MailboxUnreadStateUpdated::dispatch($this->userId($user), true);
    }

    public function sync(User $user): void
    {
        MailboxUnreadStateUpdated::dispatch(
            (int) $user->id,
            $this->mailbox->hasUnread($user),
        );
    }

    private function userId(User|int $user): int
    {
        return $user instanceof User ? (int) $user->id : $user;
    }
}
