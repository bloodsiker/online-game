<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Application\UseCases\ManageIgnore;
use App\Modules\Chat\Application\UseCases\SendMessage;
use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Events\ChatMessagesInvalidated;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Services\MessageRenderer;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ChatService
{
    public function __construct(
        private readonly SendMessage $sendMessage,
        private readonly SendSystemMessage $sendSystemMessage,
        private readonly GetMessages $getMessages,
        private readonly ManageIgnore $manageIgnore,
        private readonly MessageRenderer $renderer,
    ) {}

    public function send(User $sender, string $raw, ChatChannel $defaultChannel): ChatMessage
    {
        return $this->sendMessage->execute($sender, $raw, $defaultChannel);
    }

    public function sendSystemToUser(User $user, string $message): ChatMessage
    {
        return $this->sendSystemMessage->toUser($user, $message, ChatMessageType::Information);
    }

    public function sendPartyInviteToUser(User $user, string $message): ChatMessage
    {
        return $this->sendSystemMessage->toUser($user, $message, ChatMessageType::PartyInvite);
    }

    public function sendPartyNoticeToUser(User $user, string $message): ChatMessage
    {
        return $this->sendSystemMessage->toUser($user, $message, ChatMessageType::PartyNotice);
    }

    /** Удаляет персональное системное сообщение, не затрагивая чужой чат. */
    public function removeSystemMessageForUser(User $user, ?int $messageId): void
    {
        if ($messageId === null) {
            return;
        }

        $deleted = ChatMessage::query()
            ->whereKey($messageId)
            ->where('target_user_id', $user->id)
            ->delete();

        if ($deleted > 0) {
            ChatMessagesInvalidated::dispatch((int) $user->id);
        }
    }

    public function sendQuestToUser(User $user, string $message): ChatMessage
    {
        return $this->sendSystemMessage->toUser($user, $message, ChatMessageType::Quest);
    }

    public function sendQuestItemDropToUser(User $user, string $message): ChatMessage
    {
        return $this->sendSystemMessage->toUser($user, $message, ChatMessageType::QuestItem);
    }

    public function sendSystem(string $message, ?int $mapId = null, ?int $clanId = null): ChatMessage
    {
        return $this->sendSystemMessage->toChannel($message, $mapId, $clanId);
    }

    public function sendSystemToParty(int $partyId, string $message): ChatMessage
    {
        return $this->sendSystemMessage->toParty($partyId, $message);
    }

    public function getMessages(User $user, ChatChannel $channel, ?int $afterId = null, int $limit = 60): Collection
    {
        return $this->getMessages->execute($user, $channel, $afterId, $limit);
    }

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array
    {
        return $this->getMessages->filterValidIds($user, $channel, $ids);
    }

    public function renderMessageContent(string $message, bool $trusted = false): string
    {
        return $this->renderer->render($message, $trusted);
    }

    public function addIgnore(User $user, int $targetUserId): void
    {
        $this->manageIgnore->add($user, $targetUserId);
    }

    public function removeIgnore(User $user, int $targetUserId): void
    {
        $this->manageIgnore->remove($user, $targetUserId);
    }

    public function getIgnores(User $user): Collection
    {
        return $this->manageIgnore->list($user);
    }
}
