<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Telegram\Application\Services\TelegramBotService;
use App\Modules\Telegram\Domain\Contracts\TelegramCallbackResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TelegramWebhookController
{
    public function __invoke(Request $request, TelegramBotService $bot, TelegramCallbackResponder $callbacks): JsonResponse
    {
        $request->validate(['update_id' => ['required', 'integer', 'min:0', 'max:'.PHP_INT_MAX],
            'message' => ['sometimes', 'array'], 'message.from' => ['sometimes', 'array'],
            'message.chat' => ['sometimes', 'array'], 'message.text' => ['sometimes', 'string', 'max:4096'],
            'callback_query' => ['sometimes', 'array'], 'callback_query.id' => ['required_with:callback_query', 'string', 'min:1', 'max:256'],
            'callback_query.from' => ['sometimes', 'array'], 'callback_query.message' => ['sometimes', 'array'],
            'callback_query.message.chat' => ['sometimes', 'array'], 'callback_query.data' => ['sometimes', 'string', 'max:64']]);
        $reply = $bot->handle(update: array_replace($request->all(), ['update_id' => (int) $request->input('update_id')]));
        // Acknowledge after committing all database work; the webhook response updates the photo.
        if (($reply['method'] ?? null) === 'editMessageMedia') {
            $callbacks->acknowledge(callbackId: $request->input('callback_query.id'));
        }

        return response()->json($reply, headers: ['Cache-Control' => 'no-store, private']);
    }
}
