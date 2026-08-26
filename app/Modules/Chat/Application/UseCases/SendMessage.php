<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class SendMessage
{
    public function __construct(
        private readonly ChatMessageRepositoryInterface $repository,
        private readonly PartyRepositoryInterface $partyRepository,
    ) {}

    /**
     * Parse and send a player message.
     *
     * Supported formats:
     *   prv[NAME] - text   → private message to NAME
     *   to[NAME] - text    → public mention in current channel
     *   [[item_ID]]        → item shortcode
     *   [[share_item_ID]]  → shared item shortcode
     */
    public function execute(User $sender, string $raw, ChatChannel $defaultChannel): ChatMessage
    {
        $raw = trim($raw);

        // Private message: prv[NAME] - text  OR  prv[NAME] text
        if (preg_match('/^prv\[([^\]]+)\]\s*-?\s*(.*)/is', $raw, $matches)) {
            $targetName = trim($matches[1]);
            $text = trim($matches[2]);
            $target = User::where('name', $targetName)->first();

            return $this->repository->create([
                'user_id' => $sender->id,
                'channel' => ChatChannel::Private->value,
                'target_user_id' => $target?->id,
                'message' => $text ?: $raw,
                'type' => ChatMessageType::Private->value,
            ]);
        }

        $type = preg_match('/^to\[([^\]]+)\]/i', $raw)
            ? ChatMessageType::Mention
            : ChatMessageType::Message;

        $data = [
            'user_id' => $sender->id,
            'channel' => $defaultChannel->value,
            'message' => $raw,
            'type' => $type->value,
        ];

        if ($defaultChannel === ChatChannel::Location) {
            $data['map_id'] = $sender->currentLocation?->map_id;
        }

        if ($defaultChannel === ChatChannel::Clan) {
            $data['clan_id'] = $sender->clanMembership?->clan_id;
        }

        if ($defaultChannel === ChatChannel::Party) {
            $party = $this->partyRepository->findActiveByUser((int) $sender->id);
            if ($party === null) {
                throw new RuntimeException('Канал группы доступен только её участникам.');
            }

            $data['party_id'] = $party->id;
        }

        return $this->repository->create($data);
    }
}
