<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
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

    public function toChannel(
        string|LocalizedChatMessage $message,
        ?int $mapId = null,
        ?int $clanId = null,
        ChatMessageType $type = ChatMessageType::System,
    ): ChatMessage {
        return $this->repository->create([
            'user_id' => null,
            'channel' => ChatChannel::System->value,
            'map_id' => $mapId,
            'clan_id' => $clanId,
            ...$this->content($message),
            'type' => $type->value,
        ]);
    }

    public function toUser(User $user, string|LocalizedChatMessage $message, ChatMessageType $type = ChatMessageType::Information): ChatMessage
    {
        return $this->repository->create([
            'user_id' => null,
            'channel' => ChatChannel::Main->value,
            'target_user_id' => $user->id,
            ...$this->content($message),
            'type' => $type->value,
        ]);
    }

    public function toParty(int $partyId, string|LocalizedChatMessage $message): ChatMessage
    {
        return $this->repository->create([
            'user_id' => null,
            'channel' => ChatChannel::Party->value,
            'party_id' => $partyId,
            ...$this->content($message),
            'type' => ChatMessageType::PartyNotice->value,
        ]);
    }

    public function toClan(int $clanId, string|LocalizedChatMessage $message): ChatMessage
    {
        return $this->repository->create([
            'user_id' => null,
            'channel' => ChatChannel::Clan->value,
            'clan_id' => $clanId,
            ...$this->content($message),
            'type' => ChatMessageType::ClanBoss->value,
        ]);
    }

    private function content(string|LocalizedChatMessage $message): array
    {
        if (is_string($message)) {
            return ['message' => $message];
        }

        return [
            'message' => '',
            'translation_key' => $message->key,
            'translation_params' => $message->payload()['params'],
        ];
    }
}
