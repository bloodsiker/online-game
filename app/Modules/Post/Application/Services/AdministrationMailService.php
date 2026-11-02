<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\Services;

use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class AdministrationMailService
{
    public function __construct(private BroadcastMailboxUnreadState $unreadState) {}

    public function threads(): Builder
    {
        return PostLetter::query()->where('is_to_administration', true)
            ->whereNull('administration_thread_id');
    }

    public function thread(int $id, ?User $owner = null): PostLetter
    {
        return $this->threads()->when($owner, fn (Builder $query) => $query->where('sender_user_id', $owner->id))
            ->findOrFail($id);
    }

    public function messages(PostLetter $thread): Collection
    {
        return PostLetter::query()->with(['sender', 'administrationSender'])
            ->where(fn (Builder $query) => $query->whereKey($thread->id)->orWhere('administration_thread_id', $thread->id))
            ->orderBy('id')->get();
    }

    public function send(User $player, string $subject, string $text, ?int $threadId = null): PostLetter
    {
        return DB::transaction(function () use ($player, $subject, $text, $threadId): PostLetter {
            $thread = $threadId === null ? null : $this->threads()->where('sender_user_id', $player->id)
                ->lockForUpdate()->findOrFail($threadId);
            $letter = PostLetter::query()->create([
                'sender_user_id' => $player->id,
                'recipient_user_id' => null,
                'is_to_administration' => true,
                'administration_thread_id' => $thread?->id,
                'subject' => $thread?->subject ?? $subject,
                'text' => $text,
                'administration_status' => $thread ? null : 'pending',
                'administration_last_message_at' => $thread ? null : now(),
            ]);
            $thread?->update(['administration_status' => 'pending', 'administration_last_message_at' => now(), 'read_at' => null]);

            return $thread ?? $letter;
        });
    }

    public function reply(User $admin, int $threadId, string $text): PostLetter
    {
        abort_unless($admin->is_admin, 403);

        return DB::transaction(function () use ($admin, $threadId, $text): PostLetter {
            $thread = $this->threads()->lockForUpdate()->findOrFail($threadId);
            abort_if($thread->sender_user_id === null, 422, 'Игрок удалён.');
            $reply = PostLetter::query()->create([
                'sender_user_id' => null,
                'recipient_user_id' => $thread->sender_user_id,
                'administration_sender_id' => $admin->id,
                'administration_thread_id' => $thread->id,
                'subject' => $thread->subject,
                'text' => $text,
            ]);
            $thread->update(['administration_status' => 'answered', 'administration_last_message_at' => now(), 'read_at' => now()]);
            $this->unreadState->markUnread($thread->sender_user_id);

            return $reply;
        });
    }
}
