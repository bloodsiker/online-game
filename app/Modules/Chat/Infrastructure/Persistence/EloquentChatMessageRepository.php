<?php

declare(strict_types=1);

namespace App\Modules\Chat\Infrastructure\Persistence;

use App\Enums\PlayerRelationshipType;
use App\Models\Player\PlayerRelationship;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EloquentChatMessageRepository implements ChatMessageRepositoryInterface
{
    public function create(array $data): ChatMessage
    {
        return ChatMessage::create($data);
    }

    /** @return ChatMessage[] */
    public function getForChannel(
        User $user,
        ChatChannel $channel,
        ?int $afterId,
        int $limit,
    ): array {
        $query = ChatMessage::query()->with('sender', 'target');
        $this->applyChannelFilter($query, $user, $channel);

        if ($afterId) {
            return $query->where('id', '>', $afterId)
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get()
                ->all();
        }

        return $query->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->all();
    }

    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $query = ChatMessage::query()->whereIn('id', $ids);
        $this->applyChannelFilter($query, $user, $channel);

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function getIgnoredUserIds(User $user): array
    {
        return PlayerRelationship::where('player_relationships.player_id', $user->player_id)
            ->where('player_relationships.type', PlayerRelationshipType::IGNORE)
            ->join('users', 'player_relationships.target_id', '=', 'users.player_id')
            ->pluck('users.id')
            ->all();
    }

    private function applyChannelFilter(Builder $query, User $user, ChatChannel $channel): void
    {
        $ignoredIds = $this->getIgnoredUserIds($user);
        $tenMinutesAgo = Carbon::now()->subMinutes(10);
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);

        $privateClause = function ($q) use ($user, $ignoredIds, $tenMinutesAgo) {
            $q->where('channel', ChatChannel::Private->value)
                ->where('created_at', '>=', $tenMinutesAgo)
                ->where(fn ($q2) => $q2->where('user_id', $user->id)->orWhere('target_user_id', $user->id));
            if ($ignoredIds) {
                $q->where(fn ($q2) => $q2->whereNull('user_id')->orWhereNotIn('user_id', $ignoredIds));
            }
        };

        $privateFullClause = function ($q) use ($user, $ignoredIds) {
            $q->where('channel', ChatChannel::Private->value)
                ->where(fn ($q2) => $q2->where('user_id', $user->id)->orWhere('target_user_id', $user->id));
            if ($ignoredIds) {
                $q->where(fn ($q2) => $q2->whereNull('user_id')->orWhereNotIn('user_id', $ignoredIds));
            }
        };

        $systemClause = fn ($q) => $q->where('channel', ChatChannel::System->value)
            ->where('created_at', '>=', $thirtyMinutesAgo);

        match ($channel) {
            ChatChannel::Main => $query->where(fn ($q) => $q
                ->where(function ($q2) use ($ignoredIds) {
                    $q2->where('channel', ChatChannel::Main->value)->whereNull('target_user_id');
                    if ($ignoredIds) {
                        $q2->where(fn ($q3) => $q3->whereNull('user_id')->orWhereNotIn('user_id', $ignoredIds));
                    }
                })
                ->orWhere(fn ($q2) => $q2
                    ->where('channel', ChatChannel::Main->value)
                    ->where('target_user_id', $user->id)
                    ->whereIn('type', [ChatMessageType::Information->value, ChatMessageType::Quest->value])
                    ->where('created_at', '>=', Carbon::now()->subMinutes(10))
                )
                ->orWhere($privateClause)
                ->orWhere($systemClause)
            ),

            ChatChannel::Location => $query->where(fn ($q) => $q
                ->where(function ($q2) use ($ignoredIds, $user) {
                    $mapId = $user->currentLocation?->map_id;
                    $q2->where('channel', ChatChannel::Location->value)->where('map_id', $mapId);
                    if ($ignoredIds) {
                        $q2->whereNotIn('user_id', $ignoredIds);
                    }
                })
                ->orWhere($privateClause)
                ->orWhere($systemClause)
            ),

            ChatChannel::Trade => $query->where(fn ($q) => $q
                ->where(function ($q2) use ($ignoredIds) {
                    $q2->where('channel', ChatChannel::Trade->value);
                    if ($ignoredIds) {
                        $q2->whereNotIn('user_id', $ignoredIds);
                    }
                })
                ->orWhere($privateClause)
                ->orWhere($systemClause)
            ),

            ChatChannel::Clan => $query->where(fn ($q) => $q
                ->where(function ($q2) use ($ignoredIds, $user) {
                    $clanId = $user->clanMembership?->clan_id;
                    $q2->where('channel', ChatChannel::Clan->value)->where('clan_id', $clanId);
                    if ($ignoredIds) {
                        $q2->whereNotIn('user_id', $ignoredIds);
                    }
                })
                ->orWhere($privateClause)
                ->orWhere($systemClause)
            ),

            ChatChannel::Private => $query->where(fn ($q) => $q
                ->where($privateFullClause)
                ->orWhere($systemClause)
            ),

            ChatChannel::System => $query->where('channel', ChatChannel::System->value),
        };
    }
}
