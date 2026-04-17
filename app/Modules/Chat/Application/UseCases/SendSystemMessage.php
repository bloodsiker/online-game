<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class SendSystemMessage
{
    public function __construct(
        private readonly ChatMessageRepositoryInterface $repository,
    ) {}

    public function toChannel(string $message, ?int $mapId = null, ?int $clanId = null): ChatMessage
    {
        return $this->repository->create([
            'user_id' => null,
            'channel' => ChatChannel::System->value,
            'map_id' => $mapId,
            'clan_id' => $clanId,
            'message' => $message,
            'type' => ChatMessageType::System->value,
        ]);
    }

    public function toUser(User $user, string $message, ChatMessageType $type = ChatMessageType::Information): ChatMessage
    {
        return $this->repository->create([
            'user_id' => null,
            'channel' => ChatChannel::Main->value,
            'target_user_id' => $user->id,
            'message' => $message,
            'type' => $type->value,
        ]);
    }
}
