<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Chat\Application\Services\ChatClientService;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Telegram\Application\Services\TelegramReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\Rule;

final class TelegramChatController
{
    use Localizable;

    public function messages(Request $request, ChatClientService $chat, TelegramReadService $read): JsonResponse
    {
        $input = $request->validate([
            'channel' => ['required', Rule::enum(ChatChannel::class)],
            'after_id' => ['sometimes', 'integer', 'min:0'],
            'ids' => ['sometimes', 'array', 'max:200'], 'ids.*' => ['integer', 'min:1', 'distinct'],
        ]);
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'realtime' => ['enabled' => config('broadcasting.default') === 'reverb',
                'key' => config('broadcasting.connections.reverb.key')],
        ] + $chat->read(userId: $userId, channel: ChatChannel::from($input['channel']),
            afterId: isset($input['after_id']) ? (int) $input['after_id'] : null,
            ids: array_map('intval', $input['ids'] ?? []))));
    }

    public function send(Request $request, ChatClientService $chat, TelegramReadService $read): JsonResponse
    {
        $input = $request->validate(['channel' => ['required', Rule::enum(ChatChannel::class)], 'message' => ['required', 'string', 'max:500']]);
        $userId = $request->attributes->get('telegram.account')->userId;

        return $this->withLocale($read->locale(userId: $userId), fn (): JsonResponse => response()->json([
            'ok' => true, 'message_id' => $chat->send(userId: $userId, channel: ChatChannel::from($input['channel']), text: $input['message']),
        ]));
    }

    public function authorize(Request $request, ChatClientService $chat): JsonResponse
    {
        $input = $request->validate(['socket_id' => ['required', 'string', 'regex:/^\d+\.\d+$/D', 'max:100'],
            'channel_name' => ['required', 'string', 'max:200']]);
        $user = $chat->broadcastingUser(userId: $request->attributes->get('telegram.account')->userId, channelName: $input['channel_name']);
        $request->setUserResolver(static fn () => $user);
        abort_unless(config('broadcasting.default') === 'reverb', 503);

        return response()->json(Broadcast::auth($request));
    }
}
