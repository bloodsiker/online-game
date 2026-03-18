<?php

namespace App\Services;

use App\Enums\ChatChannel;
use App\Enums\ChatMessageType;
use App\Models\Chat\ChatIgnore;
use App\Models\Chat\ChatMessage;
use App\Models\Item\Item;
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
            $text       = trim($matches[2]);
            $target     = User::where('name', $targetName)->first();

            return ChatMessage::create([
                'user_id'        => $sender->id,
                'channel'        => ChatChannel::Private->value,
                'target_user_id' => $target?->id,
                'message'        => $text ?: $raw,
                'type'           => ChatMessageType::Private->value,
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
            'type'    => $type->value,
        ];

        if ($defaultChannel === ChatChannel::Location) {
            $data['map_id']      = $sender->currentLocation?->map_id;
        }

        if ($defaultChannel === ChatChannel::Clan) {
            $data['clan_id'] = $sender->clanMembership?->clan_id;
        }

        return ChatMessage::create($data);
    }

    /**
     * Create a system notification message.
     */
    public function sendSystem(string $message, ?int $mapId = null, ?int $clanId = null): ChatMessage
    {
        return ChatMessage::create([
            'user_id'     => null,
            'channel'     => ChatChannel::System->value,
            'map_id'      => $mapId,
            'clan_id'     => $clanId,
            'message'     => $message,
            'type'        => ChatMessageType::System->value,
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
        $ignoredIds    = $this->getIgnoredUserIds($user);
        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        $privateClause = function ($q) use ($user, $ignoredIds, $tenMinutesAgo) {
            $q->where('channel', ChatChannel::Private->value)
                ->where('created_at', '>=', $tenMinutesAgo)
                ->where(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->orWhere('target_user_id', $user->id);
                });
            if ($ignoredIds) $q->whereNotIn('user_id', $ignoredIds);
        };

        $privateFullClause = function ($q) use ($user, $ignoredIds) {
            $q->where('channel', ChatChannel::Private->value)
                ->where(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->orWhere('target_user_id', $user->id);
                });
            if ($ignoredIds) $q->whereNotIn('user_id', $ignoredIds);
        };

        $systemClause = function ($q) {
            $q->where('channel', ChatChannel::System->value);
        };

        switch ($channel) {
            case ChatChannel::Main:
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause) {
                    $q->where(function ($q2) use ($ignoredIds) {
                        $q2->where('channel', ChatChannel::Main->value);
                        if ($ignoredIds) $q2->whereNotIn('user_id', $ignoredIds);
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Location:
                $mapId = $user->currentLocation?->map_id;
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause, $mapId) {
                    $q->where(function ($q2) use ($ignoredIds, $mapId) {
                        $q2->where('channel', ChatChannel::Location->value)
                            ->where('map_id', $mapId);
                        if ($ignoredIds) $q2->whereNotIn('user_id', $ignoredIds);
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Trade:
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause) {
                    $q->where(function ($q2) use ($ignoredIds) {
                        $q2->where('channel', ChatChannel::Trade->value);
                        if ($ignoredIds) $q2->whereNotIn('user_id', $ignoredIds);
                    })->orWhere($privateClause)->orWhere($systemClause);
                });
                break;

            case ChatChannel::Clan:
                $clanId = $user->clanMembership?->clan_id;
                $query->where(function ($q) use ($ignoredIds, $privateClause, $systemClause, $clanId) {
                    $q->where(function ($q2) use ($ignoredIds, $clanId) {
                        $q2->where('channel', ChatChannel::Clan->value)
                            ->where('clan_id', $clanId);
                        if ($ignoredIds) $q2->whereNotIn('user_id', $ignoredIds);
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
    public function renderMessageContent(string $message): string
    {
        // Escape all HTML first
        $escaped = e($message);

        // Replace [[item_ID]] shortcodes (brackets are not HTML-special, so untouched by e())
        $rendered = preg_replace_callback('/\[\[item_(\d+)\]\]/', function ($matches) {
            $item = Item::with('itemInfo')->find((int) $matches[1]);

            if (! $item || ! $item->itemInfo) {
                return '<span class="chat-item-unknown" title="Предмет не найден">[???]</span>';
            }

            $name = e($item->getName());
            $desc = e($item->itemInfo->description ?? '');

            return '<span class="chat-item" title="' . $desc . '">[' . $name . ']</span>';
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
        ChatIgnore::firstOrCreate([
            'user_id'         => $user->id,
            'ignored_user_id' => $targetUserId,
        ]);
    }

    public function removeIgnore(User $user, int $targetUserId): void
    {
        ChatIgnore::where('user_id', $user->id)
            ->where('ignored_user_id', $targetUserId)
            ->delete();
    }

    public function getIgnores(User $user): Collection
    {
        return ChatIgnore::where('user_id', $user->id)->with('ignoredUser')->get();
    }

    private function getIgnoredUserIds(User $user): array
    {
        return ChatIgnore::where('user_id', $user->id)
            ->pluck('ignored_user_id')
            ->all();
    }
}
