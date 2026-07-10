<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class GetMailbox
{
    public const CAPACITY = 40;

    public const UNREAD_TTL_DAYS = 30;

    public const READ_TTL_DAYS = 3;

    /** @return Collection<int, PostLetter> */
    public function inbox(User $user): Collection
    {
        $this->pruneExpired($user);

        return PostLetter::query()
            ->with(['sender', 'shareItem'])
            ->where('recipient_user_id', $user->id)
            ->whereNull('recipient_deleted_at')
            ->orderByDesc('id')
            ->get();
    }

    /** @return Collection<int, PostLetter> */
    public function sent(User $user): Collection
    {
        return PostLetter::query()
            ->with('recipient')
            ->where('sender_user_id', $user->id)
            ->whereNull('sender_deleted_at')
            ->orderByDesc('id')
            ->get();
    }

    public function inboxCount(User $user): int
    {
        return PostLetter::query()
            ->where('recipient_user_id', $user->id)
            ->whereNull('recipient_deleted_at')
            ->count();
    }

    /**
     * Сроки хранения как на проде: непрочитанное — 30 дней, прочитанное — 3 дня.
     */
    private function pruneExpired(User $user): void
    {
        PostLetter::query()
            ->where('recipient_user_id', $user->id)
            ->whereNull('recipient_deleted_at')
            ->where(function ($q): void {
                $q->where(fn ($q2) => $q2->whereNull('read_at')->where('created_at', '<', now()->subDays(self::UNREAD_TTL_DAYS)))
                    ->orWhere(fn ($q2) => $q2->whereNotNull('read_at')->where('read_at', '<', now()->subDays(self::READ_TTL_DAYS)));
            })
            ->update(['recipient_deleted_at' => now()]);
    }
}
