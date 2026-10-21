<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Persistence;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Chat\Domain\ChatMessageLifetime;
use App\Modules\Notification\Domain\Contracts\PlayerNotificationRepository;
use App\Modules\Notification\Infrastructure\Persistence\Models\PlayerNotification;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class EloquentPlayerNotificationRepository implements PlayerNotificationRepository
{
    public function __construct(private SendSystemMessage $chat) {}

    public function available(): bool
    {
        return Schema::hasTable('player_notifications');
    }

    public function create(int $userId, string $type, string $deduplicationKey, LocalizedChatMessage $message): PlayerNotification
    {
        return PlayerNotification::query()->firstOrCreate(
            ['user_id' => $userId, 'deduplication_key' => $deduplicationKey],
            ['type' => $type, 'translation_key' => $message->key, 'translation_params' => $message->payload()['params']],
        );
    }

    public function deliver(User $user): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        return DB::transaction(function () use ($user): Collection {
            $notifications = PlayerNotification::query()->where('user_id', $user->id)->whereNull('read_at')
                ->orderBy('id')->limit(50)->lockForUpdate()->get();

            foreach ($notifications as $notification) {
                $message = $notification->chatMessage;
                if ($message === null || $message->created_at->lte(now()->subMinutes(ChatMessageLifetime::PERSONAL_MINUTES))) {
                    $message = $this->chat->toUser(user: $user, message: new LocalizedChatMessage(
                        key: $notification->translation_key, params: $notification->translation_params,
                    ));
                    $notification->update(['chat_message_id' => $message->id, 'delivered_at' => now()]);
                    $notification->setRelation('chatMessage', $message);
                }
            }

            return $notifications;
        }, attempts: 3);
    }

    public function acknowledge(int $userId, Collection $receipts): void
    {
        if (! $this->available() || $receipts->isEmpty()) {
            return;
        }

        PlayerNotification::query()->where('user_id', $userId)->whereNull('read_at')
            ->where(function ($query) use ($receipts): void {
                foreach ($receipts as $receipt) {
                    $query->orWhere(fn ($match) => $match->whereKey($receipt['id'])->where('chat_message_id', $receipt['message_id']));
                }
            })->update(['read_at' => now()]);
    }
}
