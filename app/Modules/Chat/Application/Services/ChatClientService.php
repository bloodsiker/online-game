<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Application\UseCases\SendMessage;
use App\Modules\Chat\Domain\Contracts\ChatParticipantReader;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Moderation\Domain\Exceptions\UserMutedException;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** A session-independent entry point; game chat remains the source of messages and access rules. */
final class ChatClientService
{
    public function __construct(
        private readonly ChatParticipantReader $participants,
        private readonly PartyRepositoryInterface $parties,
        private readonly FriendRelationshipRepository $friends,
        private readonly GetMessages $messages,
        private readonly SendMessage $sender,
        private readonly CommunicationMuteService $mutes,
    ) {}

    /** @param list<int> $ids
     * @return array<string, mixed>
     */
    public function read(int $userId, ChatChannel $channel, ?int $afterId, array $ids): array
    {
        $user = $this->user(userId: $userId);
        $channels = $this->channels(user: $user);
        $this->requireChannel(channel: $channel, channels: $channels);
        $limit = $afterId === null ? 60 : 100;
        $messages = $this->messages->execute(user: $user, channel: $channel, afterId: $afterId, limit: $limit);
        $mute = $this->mutes->activeMute(user: $user, scope: CommunicationScope::Chat);

        return [
            'messages' => $messages, 'channel' => $channel->value,
            'channels' => array_map(static fn (string $key): array => ['key' => $key, 'label' => __('telegram.chat_channel_'.$key)], array_keys($channels)),
            'subscriptions' => array_values(array_unique(array_filter(['App.Models.User.'.$userId, 'chat.system', $channels[$channel->value]]))),
            'valid_ids' => $this->messages->filterValidIds(user: $user, channel: $channel, ids: $ids),
            'cursor' => $messages ? max(array_column($messages, 'id')) : ($afterId ?? 0),
            'more' => count($messages) === $limit,
            'mute' => $mute === null ? null : ['expires_at' => $mute->expires_at->toIso8601String(),
                'message' => __('telegram.chat_muted', ['time' => $mute->expires_at->format('d.m.Y H:i'), 'reason' => $mute->reason ?? '—'])],
        ];
    }

    public function send(int $userId, ChatChannel $channel, string $text): int
    {
        $user = $this->user(userId: $userId);
        $this->requireChannel(channel: $channel, channels: $this->channels(user: $user));
        $text = trim($text);
        $this->require(condition: $text !== '', key: 'chat_empty_message');
        $private = preg_match('/^prv\[([^\]]+)\]\s*-?\s*(.*)/is', $text, $matches) === 1;
        if ($private) {
            $this->require(condition: trim($matches[2]) !== '', key: 'chat_empty_message');
            $target = $this->participants->byNickname(nickname: trim($matches[1]));
            $this->require(condition: $target !== null, key: 'chat_player_not_found');
            $this->require(condition: (int) $target->id !== $userId, key: 'chat_private_self');
            $this->require(condition: $target->last_online_at !== null
                && $target->last_online_at->gt(now()->subMinutes((int) config('game.online_timeout_minutes', 30))), key: 'chat_player_offline');
            $this->require(condition: ! $this->friends->isIgnoring(playerId: (int) $target->player_id, targetId: (int) $user->player_id), key: 'chat_ignored');
        } else {
            $this->require(condition: $channel !== ChatChannel::Private, key: 'chat_private_recipient');
        }
        try {
            return (int) $this->sender->execute(sender: $user, raw: $text, defaultChannel: $channel)->id;
        } catch (UserMutedException $exception) {
            throw ValidationException::withMessages(['message' => __('telegram.chat_muted', [
                'time' => $exception->mute->expires_at->format('d.m.Y H:i'), 'reason' => $exception->mute->reason ?? '—',
            ])]);
        } catch (RuntimeException $exception) {
            if ($exception::class !== RuntimeException::class) {
                throw $exception;
            }
            // Membership/tax may change while processing the request.
            throw ValidationException::withMessages(['channel' => __('telegram.chat_unavailable_channel')]);
        }
    }

    public function broadcastingUser(int $userId, string $channelName): User
    {
        $user = $this->user(userId: $userId);
        $allowed = array_merge(['App.Models.User.'.$userId, 'chat.system'], array_filter(array_values($this->channels(user: $user))));
        abort_unless(in_array($channelName, array_map(static fn (string $name): string => 'private-'.$name, $allowed), true), 403);

        return $user;
    }

    private function user(int $userId): User
    {
        $user = $this->participants->verified(userId: $userId);
        abort_unless($user?->player !== null, 403, __('telegram.account_unavailable'));

        return $user;
    }

    /** @return array<string, ?string> */
    private function channels(User $user): array
    {
        $channels = ['main' => 'chat.main', 'trade' => 'chat.trade'];
        if ($user->currentLocation?->map_id !== null) {
            $channels['location'] = 'chat.location.'.$user->currentLocation->map_id;
        }
        if ($user->clanMembership?->clan?->hasPaidTax() === true) {
            $channels['clan'] = 'chat.clan.'.$user->clanMembership->clan_id;
        }
        $party = $this->parties->findActiveByUser(userId: (int) $user->id);
        if ($party !== null) {
            $channels['party'] = 'chat.party.'.$party->id;
        }
        $channels['private'] = null;

        return $channels;
    }

    /** @param array<string, ?string> $channels */
    private function requireChannel(ChatChannel $channel, array $channels): void
    {
        $this->require(condition: array_key_exists($channel->value, $channels), key: 'chat_unavailable_channel', field: 'channel');
    }

    private function require(bool $condition, string $key, string $field = 'message'): void
    {
        if (! $condition) {
            throw ValidationException::withMessages([$field => __('telegram.'.$key)]);
        }
    }
}
