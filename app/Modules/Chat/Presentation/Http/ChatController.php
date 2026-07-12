<?php

declare(strict_types=1);

namespace App\Modules\Chat\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Application\UseCases\ManageIgnore;
use App\Modules\Chat\Application\UseCases\SendMessage;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly SendMessage $sendMessage,
        private readonly GetMessages $getMessages,
        private readonly ManageIgnore $manageIgnore,
        private readonly FriendRelationshipRepository $friendRelationshipRepository,
    ) {}

    public function index(): View
    {
        return view('chat::index');
    }

    public function chat(Request $request, FriendRelationshipRepository $friendRepository): View
    {
        $channel = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;
        $user = auth()->user();
        $messages = $this->getMessages->execute($user, $channel);

        return view('chat::chat', [
            'messages' => $messages,
            'channel' => $channel,
            'ignoredUserIds' => $friendRepository->getIgnoredUserIdsByPlayerId((int) $user->player_id),
        ]);
    }

    public function chatLog(): View
    {
        return view('chat::log');
    }

    public function chatAction(Request $request): View
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
        $raw = $request->input('message');

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

            $isIgnored = $this->friendRelationshipRepository->isIgnoring(
                (int) $target->player_id,
                (int) auth()->user()->player_id,
            );

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
        $channel = ChatChannel::tryFrom($request->query('channel', 'main')) ?? ChatChannel::Main;
        $user = auth()->user();
        $afterId = $request->query('after_id');
        $afterId = is_numeric($afterId) ? max(0, (int) $afterId) : null;
        $messages = $this->getMessages->execute($user, $channel, $afterId, $afterId === null ? 30 : 100);

        return response()->json($messages);
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

    public function ignoreList(): View
    {
        $ignores = $this->manageIgnore->list(auth()->user());

        return view('chat::ignores', compact('ignores'));
    }
}
