<?php

declare(strict_types=1);

namespace App\Modules\Notification\Domain\Contracts;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Notification\Infrastructure\Persistence\Models\PlayerNotification;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface PlayerNotificationRepository
{
    public function available(): bool;

    public function create(int $userId, string $type, string $deduplicationKey, LocalizedChatMessage $message): PlayerNotification;

    /** @return Collection<int, PlayerNotification> */
    public function deliver(User $user): Collection;

    /** @param Collection<int, array{id: int, message_id: int}> $receipts */
    public function acknowledge(int $userId, Collection $receipts): void;
}
