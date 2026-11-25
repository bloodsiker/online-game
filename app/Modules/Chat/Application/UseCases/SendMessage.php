<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use RuntimeException;

class SendMessage
{
    private const RAND_MIN = 0;

    /** Верхняя граница по умолчанию для /rand без числа. */
    private const RAND_DEFAULT_MAX = 100;

    /** Потолок, чтобы число в сообщении оставалось читаемым. */
    private const RAND_LIMIT = 1_000_000;

    /** Русский вариант для хранения; каждому игроку текст рисуется на его языке по ключу chat.rand_result_max. */
    private const RAND_MESSAGE = 'Кинул случайное число %d из %d';

    public function __construct(
        private readonly ChatMessageRepositoryInterface $repository,
        private readonly PartyRepositoryInterface $partyRepository,
        private readonly CommunicationMuteService $communicationMuteService,
    ) {}

    /**
     * Parse and send a player message.
     *
     * Supported formats:
     *   prv[NAME] - text   → private message to NAME
     *   to[NAME] - text    → public mention in current channel
     *   [[item_ID]]        → item shortcode
     *   [[share_item_ID]]  → shared item shortcode
     *   /rand              → случайное число от 0 до 100 в текущий канал
     *   /rand N            → случайное число от 0 до N (1…1 000 000)
     */
    public function execute(User $sender, string $raw, ChatChannel $defaultChannel): ChatMessage
    {
        $this->communicationMuteService->throwIfMuted($sender, CommunicationScope::Chat);

        $raw = trim($raw);

        // Команда /rand [N]: вместо текста в канал уходит случайное число от 0 до N (по умолчанию 100), его видят все в канале.
        $isRand = preg_match('/^\/rand(?:\s+(\d{1,9}))?$/i', $raw, $randMatch) === 1
            && (! isset($randMatch[1]) || (int) $randMatch[1] >= 1);
        $randNumber = null;
        $randMax = self::RAND_DEFAULT_MAX;
        if ($isRand) {
            $randMax = isset($randMatch[1]) ? min((int) $randMatch[1], self::RAND_LIMIT) : self::RAND_DEFAULT_MAX;
            $randNumber = random_int(self::RAND_MIN, $randMax);
            $raw = sprintf(self::RAND_MESSAGE, $randNumber, $randMax);
        }

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

        $type = match (true) {
            $isRand => ChatMessageType::Rand,
            preg_match('/^to\[([^\]]+)\]/i', $raw) === 1 => ChatMessageType::Mention,
            default => ChatMessageType::Message,
        };

        $data = [
            'user_id' => $sender->id,
            'channel' => $defaultChannel->value,
            'message' => $raw,
            'type' => $type->value,
        ];

        if ($randNumber !== null) {
            $data['translation_key'] = 'chat.rand_result_max';
            $data['translation_params'] = ['number' => $randNumber, 'max' => $randMax];
        }

        if ($defaultChannel === ChatChannel::Location) {
            $data['map_id'] = $sender->currentLocation?->map_id;
        }

        if ($defaultChannel === ChatChannel::Clan) {
            $sender->loadMissing('clanMembership.clan');
            $membership = $sender->clanMembership;

            if ($membership === null) {
                throw new RuntimeException('Вы не состоите в клане.');
            }

            if (! $membership->clan->hasPaidTax()) {
                throw new RuntimeException('Клановый чат заблокирован до оплаты ежемесячного налога.');
            }

            $data['clan_id'] = $membership->clan_id;
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
