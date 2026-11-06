<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Telegram\Application\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TelegramWebhookController
{
    public function __invoke(Request $request, TelegramBotService $bot): JsonResponse
    {
        $request->validate(['update_id' => ['required', 'integer', 'min:0', 'max:'.PHP_INT_MAX],
            'message' => ['sometimes', 'array'], 'message.from' => ['sometimes', 'array'],
            'message.chat' => ['sometimes', 'array'], 'message.text' => ['sometimes', 'string', 'max:4096']]);

        return response()->json($bot->handle(update: array_replace($request->all(), ['update_id' => (int) $request->input('update_id')])), headers: ['Cache-Control' => 'no-store, private']);
    }
}
