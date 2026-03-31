<?php

namespace App\Services;

use App\Enums\ChatChannel;
use App\Enums\ChatMessageType;
use App\Enums\PlayerRelationshipType;
use App\Models\Chat\ChatMessage;
use App\Models\Item\Item;
use App\Models\Player\PlayerRelationship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ChatService
{
    /**
     * Parse and send a message.
     *
     * Supported formats:
     *   prv[NAME] - text   → private message to NAME
     *   to[NAME] - text    → public mention in current channel
     *   [[item_ID]]        → item shortcode, stored as-is and rendered at display time
     */
    public function send(User $sender, string $raw, ChatChannel $defaultChannel): ChatMessage
    {
        $raw = trim($raw);

        // Private message: prv[NAME] - text  OR  prv[NAME] text
        if (preg_match('/^prv\[([^\]]+)\]\s*-?\s*(.*)/is', $raw, $matches)) {
            $targetName = trim($matches[1]);
            $text = trim($matches[2]);
            $target = User::where('name', $targetName)->first();

            return ChatMessage::create([
                'user_id' => $sender->id,
                'channel' => ChatChannel::Private->value,
                'target_user_id' => $target?->id,
                'message' => $text ?: $raw,
                'type' => ChatMessageType::Private->value,
            ]);
        }

        // Mention: to[NAME] ...
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

        return ChatMessage::create($data);
    }

    /**
     * Send a personal system message visible only to the specified user.
     * Appears in the Private channel tab, styled as a system message.
     */
    public function sendSystemToUser(User $user, string $message): ChatMessage
    {
        return ChatMessage::create([
            'user_id'        => null,
            'channel'        => ChatChannel::Main->value,
            'target_user_id' => $user->id,
            'message'        => $message,
            'type'           => ChatMessageType::Information->value,
        ]);
    }

    /**
     * Send a personal quest notification visible only to the specified user.
     * Displayed in italic black, same visibility rules as sendSystemToUser.
     */
    public function sendQuestToUser(User $user, string $message): ChatMessage
    {
        return ChatMessage::create([
            'user_id'        => null,
            'channel'        => ChatChannel::Main->value,
            'target_user_id' => $user->id,
            'message'        => $message,
            'type'           => ChatMessageType::Quest->value,
        ]);
    }

    /**
     * Create a system notification message.
     */
    public function sendSystem(string $message, ?int $mapId = null, ?int $clanId = null): ChatMessage
    {
        return ChatMessage::create([
            'user_id' => null,
            'channel' => ChatChannel::System->value,
            'map_id' => $mapId,
            'clan_id' => $clanId,
            'message' => $message,
            'type' => ChatMessageType::System->value,
        ]);
    }

    /**
     * Retrieve messages for a channel, filtered by ignore list.
     * If $afterId is null, returns the last $limit messages in chronological order.
     * If $afterId is set, returns messages newer than that ID.
     */
    public function getMessages(User $user, ChatChannel $channel, ?int $afterId = null, int $limit = 60): Collection
    {
        $query = ChatMessage::query()->with('sender', 'target');
        $this->applyChannelFilter($query, $user, $channel);

        if ($afterId) {
            return $query->where('id', '>', $afterId)
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get();
        }

        return $query->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * From a list of message IDs the client currently shows,
     * return only those that are still valid for this channel.
     */
    public function filterValidIds(User $user, ChatChannel $channel, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $query = ChatMessage::query()->whereIn('id', $ids);
        $this->applyChannelFilter($query, $user, $channel);

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function applyChannelFilter(\Illuminate\Database\Eloquent\Builder $query, User $user, ChatChannel $channel): void
    {
        $ignoredIds = $this->getIgnoredUserIds($user);
        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        $privateClause = function ($q) use ($user, $ignoredIds, $tenMinutesAgo) {
            $q->where('channel', ChatChannel::Private->value)
                ->where('created_at', '>=', $tenMinutesAgo)
                ->where(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->orWhere('target_user_id', $user->id);
                });
            if ($ignoredIds) {
                $q->where(function ($q2) use ($ignoredIds) {
                    $q2->whereNull('user_id')->orWhereNotIn('user_id', $ignoredIds);
                });
            }
        };

        $privateFullClause = function ($q) use ($user, $ignoredIds) {
            $q->where('channel', ChatChannel::Private->value)
                ->where(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->orWhere('target_user_id', $user->id);
                });
            if ($ignoredIds) {
                $q->where(function ($q2) use ($ignoredIds) {
                    $q2->whereNull('user_id')->orWhereNotIn('user_id', $ignoredIds);
                });
            }
        };

        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);
        $systemClause = function ($q) use ($thirtyMinutesAgo) {
            $q->where('channel', ChatChannel::System->value)
              ->where('created_at', '>=', $thirtyMinutesAgo);
        };

        switch ($channel) {
            case ChatChannel::Main:
                $query->where(function ($q) use ($user, $ignoredIds, $privateClause, $systemClause, $tenMinutesAgo) {
                    $q->where(function ($q2) use ($ignoredIds) {
                        // Regular main channel messages (not targeted)
                        $q2->where('channel', ChatChannel::Main->value)
                            ->whereNull('target_user_id');
                        if ($ignoredIds) {
                            $q2->where(function ($q3) use ($ignoredIds) {
                                $q3->whereNull('user_id')->orWhereNotIn('user_id', $ignoredIds);
                            });
                        }
                    })->orWhere(function ($q2) use ($user, $tenMinutesAgo) {
                        // Personal information/quest messages addressed to this user, visible for 10 min
                        $q2->where('channel', ChatChannel::Main->value)
                            ->where('target_user_id', $user->id)
                            ->whereIn('type', [
                                ChatMessageType::Information->value,
                                ChatMessageType::Quest->value,
                            ])
                            ->where('created_at', '>=', $tenMinutesAgo);
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Location:
                $mapId = $user->currentLocation?->map_id;
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause, $mapId) {
                    $q->where(function ($q2) use ($ignoredIds, $mapId) {
                        $q2->where('channel', ChatChannel::Location->value)
                            ->where('map_id', $mapId);
                        if ($ignoredIds) {
                            $q2->whereNotIn('user_id', $ignoredIds);
                        }
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Trade:
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause) {
                    $q->where(function ($q2) use ($ignoredIds) {
                        $q2->where('channel', ChatChannel::Trade->value);
                        if ($ignoredIds) {
                            $q2->whereNotIn('user_id', $ignoredIds);
                        }
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Clan:
                $clanId = $user->clanMembership?->clan_id;
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause, $clanId) {
                    $q->where(function ($q2) use ($ignoredIds, $clanId) {
                        $q2->where('channel', ChatChannel::Clan->value)
                            ->where('clan_id', $clanId);
                        if ($ignoredIds) {
                            $q2->whereNotIn('user_id', $ignoredIds);
                        }
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Private:
                $query->where(function ($q) use ($privateFullClause, $systemClause) {
                    $q->where($privateFullClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::System:
                $query->where('channel', ChatChannel::System->value);
                break;
        }
    }

    /**
     * Escape user input and render shortcodes as safe HTML.
     *
     * [[item_ID]]  → styled item name span
     * to[NAME]     → styled mention prefix
     */
    public function renderMessageContent(string $message, bool $trusted = false): string
    {
        // Trusted messages (system/information/quest) contain safe server-generated HTML — skip escaping
        $escaped = $trusted ? $message : e($message);

        // Replace [[item_ID]] shortcodes (brackets are not HTML-special, so untouched by e())
        $rendered = preg_replace_callback('/\[\[item_(\d+)\]\]/', function ($matches) {
            $item = Item::with('itemInfo')->find((int) $matches[1]);

            if (! $item || ! $item->itemInfo) {
                return '<span class="chat-item-unknown" title="Предмет не найден">[???]</span>';
            }

            $name = e($item->getName());
            $desc = e($item->itemInfo->description ?? '');

            return '<span class="chat-item" title="'.$desc.'">['.$name.']</span>';
        }, $escaped);

        // Highlight to[NAME] prefix
        $rendered = preg_replace(
            '/^to\[([^\]]+)\]\s*-?\s*/u',
            '<span class="chat-to">»$1</span> ',
            $rendered
        );

        return $rendered;
    }

    public function addIgnore(User $user, int $targetUserId): void
    {
        $targetUser = User::find($targetUserId);
        if (!$targetUser?->player_id) {
            return;
        }

        PlayerRelationship::firstOrCreate([
            'player_id' => $user->player_id,
            'target_id' => $targetUser->player_id,
            'type'      => PlayerRelationshipType::IGNORE,
        ]);
    }

    public function removeIgnore(User $user, int $targetUserId): void
    {
        $targetUser = User::find($targetUserId);
        if (!$targetUser?->player_id) {
            return;
        }

        PlayerRelationship::where('player_id', $user->player_id)
            ->where('target_id', $targetUser->player_id)
            ->where('type', PlayerRelationshipType::IGNORE)
            ->delete();
    }

    public function getIgnores(User $user): Collection
    {
        return PlayerRelationship::where('player_id', $user->player_id)
            ->where('type', PlayerRelationshipType::IGNORE)
            ->with('target.user')
            ->get();
    }

    private function getIgnoredUserIds(User $user): array
    {
        return PlayerRelationship::where('player_relationships.player_id', $user->player_id)
            ->where('player_relationships.type', PlayerRelationshipType::IGNORE)
            ->join('users', 'player_relationships.target_id', '=', 'users.player_id')
            ->pluck('users.id')
            ->all();
    }
}
