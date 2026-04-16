<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Application\DTOs\ChatMessageDTO;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Services\MessageRenderer;
use App\Models\User;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;

class GetMessages
{
    public function __construct(
        private readonly ChatMessageRepositoryInterface $repository,
        private readonly MessageRenderer $renderer,
    ) {}

    /**
     * If $afterId is null, returns last $limit messages in chronological order.
     * If $afterId is set, returns messages newer than that ID.
     *
     * @return ChatMessageDTO[]
     */
    public function execute(User $user, ChatChannel $channel, ?int $afterId = null, int $limit = 60): array
    {
        $messages = $this->repository->getForChannel($user, $channel, $afterId, $limit);

        return array_map(fn(ChatMessage $msg) => $this->mapToDTO($msg, $user), $messages);
    }

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array
    {
        return $this->repository->filterValidIds($user, $channel, $ids);
    }

    private function mapToDTO(ChatMessage $msg, User $user): ChatMessageDTO
    {
        $senderName = $msg->sender?->name ?? 'Система';
        $targetName = $msg->target?->name;
        $trusted    = in_array($msg->type, [ChatMessageType::System, ChatMessageType::Information, ChatMessageType::Quest]);
        $content    = $this->renderer->render($msg->message, $trusted);
        $isOwn      = $msg->user_id === $user->id;

        $replyTo = null;
        if ($msg->type === ChatMessageType::Private) {
            $replyTo = $isOwn ? $targetName : $senderName;
        }

        return new ChatMessageDTO(
            id: $msg->id,
            type: $msg->type->value,
            channel: $msg->channel->value,
            sender_name: $senderName,
            target_name: $targetName,
            sender_id: $msg->user_id ?? 0,
            content: $content,
            time: $msg->created_at->format('H:i'),
            is_own: $isOwn,
            reply_to: $replyTo,
        );
    }
}