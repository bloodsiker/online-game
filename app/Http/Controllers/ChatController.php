<?php

namespace App\Http\Controllers;

use App\Enums\ChatChannel;
use App\Enums\ChatMessageType;
use App\Enums\PlayerRelationshipType;
use App\Models\Chat\ChatMessage;
use App\Models\Player\PlayerRelationship;
use App\Models\User;
use App\Services\ChatService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index()
    {
        return view('chat.index');
    }

    public function chat(Request $request)
    {
        $channel  = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;
        $user     = auth()->user();
        $messages = $this->chatService->getMessages($user, $channel);

        $formatted = $messages->map(fn ($msg) => $this->formatMessage($msg, $user));

        return view('chat.chat', [
            'messages' => $formatted,
            'channel'  => $channel,
        ]);
    }

    public function chatLog()
    {
        return view('chat.log');
    }

    public function chatAction(Request $request)
    {
        $channel = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;

        return view('chat.chat-action', compact('channel'));
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'channel' => 'required|string',
        ]);

        $channel = ChatChannel::tryFrom($request->input('channel')) ?? ChatChannel::Main;
        $raw     = $request->input('message');

        // Private message: check target is online and not self
        if (preg_match('/^prv\[([^\]]+)\]\s*-?\s*(.*)/is', $raw, $m)) {
            if (trim($m[2]) === '') {
                return response()->json(['ok' => false, 'error' => 'Введите текст сообщения.']);
            }
            $target = User::where('name', trim($m[1]))->first();

            if (! $target) {
                return response()->json(['ok' => false, 'error' => 'Игрок не найден.']);
            }

            if ($target->id === auth()->id()) {
                return response()->json(['ok' => false, 'error' => 'Нельзя отправить приватное сообщение самому себе.']);
            }

            $isOnline = $target->last_online_at
                && $target->last_online_at->gt(Carbon::now()->subMinutes(10));

            if (! $isOnline) {
                return response()->json(['ok' => false, 'error' => "Игрок {$target->name} не в сети."]);
            }

            $isIgnored = PlayerRelationship::where('player_id', $target->player_id)
                ->where('target_id', auth()->user()->player_id)
                ->where('type', PlayerRelationshipType::IGNORE)
                ->exists();

            if ($isIgnored) {
                return response()->json(['ok' => false, 'error' => "Игрок {$target->name} добавил вас в список игнора."]);
            }
        }

        $isPrivate = (bool) preg_match('/^prv\[/i', $raw);

        if (! $isPrivate && $channel === ChatChannel::Clan && ! auth()->user()->clanMembership) {
            return response()->json(['ok' => false, 'error' => 'Вы не состоите в клане.']);
        }

        $this->chatService->send(auth()->user(), $raw, $channel);

        return response()->json(['ok' => true]);
    }

    public function messages(Request $request): JsonResponse
    {
        $channel  = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;
        $user     = auth()->user();

        $messages = $this->chatService->getMessages($user, $channel, null, 30);

        return response()->json(
            $messages->map(fn ($msg) => $this->formatMessage($msg, $user))->values()
        );
    }

    public function addIgnore(Request $request): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $this->chatService->addIgnore(auth()->user(), (int) $request->input('user_id'));

        return response()->json(['ok' => true]);
    }

    public function removeIgnore(int $userId): JsonResponse
    {
        $this->chatService->removeIgnore(auth()->user(), $userId);

        return response()->json(['ok' => true]);
    }

    public function ignoreList()
    {
        $ignores = $this->chatService->getIgnores(auth()->user());

        return view('chat.ignores', compact('ignores'));
    }

    private function formatMessage(ChatMessage $msg, $user): array
    {
        $senderName = $msg->sender?->name ?? 'Система';
        $targetName = $msg->target?->name;
        $trusted    = in_array($msg->type, [ChatMessageType::System, ChatMessageType::Information, ChatMessageType::Quest]);
        $content    = $this->chatService->renderMessageContent($msg->message, $trusted);
        $isOwn      = $msg->user_id === $user->id;

        // For private: the name to pre-fill when clicking the timestamp (always the OTHER person)
        $replyTo = null;
        if ($msg->type === ChatMessageType::Private) {
            $replyTo = $isOwn ? $targetName : $senderName;
        }

        return [
            'id'          => $msg->id,
            'type'        => $msg->type->value,
            'channel'     => $msg->channel->value,
            'sender_name' => $senderName,
            'target_name' => $targetName,
            'sender_id'   => $msg->user_id,
            'content'     => $content,
            'time'        => $msg->created_at->format('H:i'),
            'is_own'      => $isOwn,
            'reply_to'    => $replyTo,
        ];
    }
}
