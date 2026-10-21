<?php

declare(strict_types=1);

namespace App\Modules\Notification\Application\Services;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Notification\Domain\Contracts\PlayerNotificationRepository;
use App\Modules\Notification\Domain\Events\PlayerNotificationAvailable;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

final readonly class PlayerNotificationService
{
    public function __construct(private PlayerNotificationRepository $repository) {}

    /** Returns false only while the migration has not been applied. */
    public function notify(int $userId, string $type, string $deduplicationKey, LocalizedChatMessage $message): bool
    {
        if (! $this->repository->available()) {
            return false;
        }
        $notification = $this->repository->create(userId: $userId, type: $type, deduplicationKey: $deduplicationKey, message: $message);
        if ($notification->wasRecentlyCreated) {
            PlayerNotificationAvailable::dispatch($userId);
        }

        return true;
    }

    public function pending(User $user): Collection
    {
        return $this->repository->deliver(user: $user);
    }

    /** @param Collection<int, array{id: int, message_id: int}> $receipts */
    public function acknowledge(int $userId, Collection $receipts): void
    {
        $this->repository->acknowledge(userId: $userId, receipts: $receipts);
    }
}
