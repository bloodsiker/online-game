<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Application\DTOs\ChatMessageDTO;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Chat\Domain\ChatMessageLifetime;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Chat\Domain\Services\MessageRenderer;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Storage;

class GetMessages
{
    public function __construct(
        private readonly ChatMessageRepositoryInterface $repository,
        private readonly MessageRenderer $renderer,
        private readonly ChatMessageTranslator $translator,
        private readonly GameSettingsService $settings,
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

        $locale = $this->settings->get((int) $user->id)['language'];

        return array_map(fn (ChatMessage $msg) => $this->mapToDTO($msg, $user, $channel, $locale), $messages);
    }

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array
    {
        return $this->repository->filterValidIds($user, $channel, $ids);
    }

    private function mapToDTO(ChatMessage $msg, User $user, ChatChannel $viewChannel, string $locale): ChatMessageDTO
    {
        $senderName = $msg->sender?->name ?? 'Система';
        $targetName = $msg->target?->name;
        if ($msg->type === ChatMessageType::Mention && $targetName === null && preg_match('/^to\[([^\]]+)\]/i', $msg->message, $matches)) {
            $targetName = trim($matches[1]);
        }
        $trusted = in_array($msg->type, [
            ChatMessageType::System,
            ChatMessageType::Information,
            ChatMessageType::PartyInvite,
            ChatMessageType::PartyNotice,
            ChatMessageType::Quest,
            ChatMessageType::QuestItem,
            ChatMessageType::Loot,
            ChatMessageType::WorldEvent,
            ChatMessageType::ClanBoss,
            ChatMessageType::ClanInvite,
        ]);
        $text = $msg->message;
        if ($trusted && $msg->translation_key !== null) {
            $text = $this->translator->render(new LocalizedChatMessage($msg->translation_key, $msg->translation_params ?? []), $locale) ?: $text;
        }
        $content = $this->renderer->render($text, $trusted);
        $isOwn = $msg->user_id === $user->id;

        $replyTo = null;
        if ($msg->type === ChatMessageType::Private) {
            $replyTo = $isOwn ? $targetName : $senderName;
        }

        $clan = $msg->sender?->clanMembership?->clan;

        return new ChatMessageDTO(
            id: $msg->id,
            type: $msg->type->value,
            channel: $msg->channel->value,
            sender_name: $senderName,
            target_name: $targetName,
            target_user_id: $msg->target_user_id === null ? null : (int) $msg->target_user_id,
            sender_id: $msg->user_id ?? 0,
            content: $content,
            time: $msg->created_at->format('H:i'),
            is_own: $isOwn,
            reply_to: $replyTo,
            sender_level: $msg->sender?->player?->lvl,
            sender_clan_icon: $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
            sender_clan_id: $clan?->id,
            expires_at: $this->expirationTime($msg, $viewChannel),
        );
    }

    private function expirationTime(ChatMessage $message, ChatChannel $viewChannel): ?string
    {
        $expiresAt = match (true) {
            $message->channel === ChatChannel::Clan && $message->type === ChatMessageType::ClanBoss => $message->created_at->copy()->addMinutes(ChatMessageLifetime::CLAN_SYSTEM_MINUTES),
            $message->channel === ChatChannel::System => $message->created_at->copy()->addMinutes(ChatMessageLifetime::SYSTEM_MINUTES),
            $message->channel === ChatChannel::Private && $viewChannel !== ChatChannel::Private => $message->created_at->copy()->addMinutes(ChatMessageLifetime::PERSONAL_MINUTES),
            $message->channel === ChatChannel::Main && $message->target_user_id !== null => $message->created_at->copy()->addMinutes(ChatMessageLifetime::PERSONAL_MINUTES),
            default => null,
        };

        return $expiresAt?->toIso8601String();
    }
}
