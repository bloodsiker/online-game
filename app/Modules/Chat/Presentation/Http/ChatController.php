<?php

declare(strict_types=1);

namespace App\Modules\Chat\Presentation\Http;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Enums\PlayerRelationshipType;
use App\Http\Controllers\Controller;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Models\Player\PlayerRelationship;
use App\Models\User;
use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Application\UseCases\ManageIgnore;
use App\Modules\Chat\Application\UseCases\SendMessage;
use App\Modules\Chat\Domain\Services\MessageRenderer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private readonly SendMessage $sendMessage,
        private readonly GetMessages $getMessages,
        private readonly ManageIgnore $manageIgnore,
        private readonly MessageRenderer $renderer,
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('chat::index');
    }

    public function chat(Request $request): \Illuminate\View\View
    {
        $channel  = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;
        $user     = auth()->user();
        $messages = $this->getMessages->execute($user, $channel);

        $formatted = $messages->map(fn ($msg) => $this->formatMessage($msg, $user));

        return view('chat::chat', [
            'messages' => $formatted,
            'channel'  => $channel,
        ]);
    }

    public function chatLog(): \Illuminate\View\View
    {
        return view('chat::log');
    }

    public function chatAction(Request $request): \Illuminate\View\View
    {
        $channel = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;

        return view('chat::chat-action', compact('channel'));
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'channel' => 'required|string',
        ]);

        $channel = ChatChannel::tryFrom($request->input('channel')) ?? ChatChannel::Main;
        $raw     = $request->input('message');

        // Private message validation
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

        $this->sendMessage->execute(auth()->user(), $raw, $channel);

        return response()->json(['ok' => true]);
    }

    public function messages(Request $request): JsonResponse
    {
        $channel  = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;
        $user     = auth()->user();
        $messages = $this->getMessages->execute($user, $channel, null, 30);

        return response()->json(
            $messages->map(fn ($msg) => $this->formatMessage($msg, $user))->values()
        );
    }

    public function addIgnore(Request $request): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $this->manageIgnore->add(auth()->user(), (int) $request->input('user_id'));

        return response()->json(['ok' => true]);
    }

    public function removeIgnore(int $userId): JsonResponse
    {
        $this->manageIgnore->remove(auth()->user(), $userId);

        return response()->json(['ok' => true]);
    }

    public function ignoreList(): \Illuminate\View\View
    {
        $ignores = $this->manageIgnore->list(auth()->user());

        return view('chat::ignores', compact('ignores'));
    }

    private function formatMessage(ChatMessage $msg, User $user): array
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