<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http;

use App\Modules\Telegram\Application\Services\TelegramReadService;
use App\Modules\Telegram\Presentation\Http\Requests\TelegramBackpackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TelegramApiController
{
    public function backpack(TelegramBackpackRequest $request, TelegramReadService $read): JsonResponse
    {
        return response()->json(['ok' => true] + $read->backpack(
            userId: $request->attributes->get('telegram.account')->userId,
            category: $request->category(), page: $request->pageNumber(), perPage: $request->pageSize(),
        ));
    }

    public function character(Request $request, TelegramReadService $read): JsonResponse
    {
        return response()->json(['ok' => true, 'data' => $read->character(userId: $request->attributes->get('telegram.account')->userId)]);
    }

    public function auction(Request $request, TelegramReadService $read): JsonResponse
    {
        return $this->listings(request: $request, read: $read, type: 'auction');
    }

    public function exchange(Request $request, TelegramReadService $read): JsonResponse
    {
        return $this->listings(request: $request, read: $read, type: 'exchange');
    }

    private function listings(Request $request, TelegramReadService $read, string $type): JsonResponse
    {
        $input = $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']]);

        return response()->json(['ok' => true] + $read->listings(
            userId: $request->attributes->get('telegram.account')->userId, type: $type,
            page: (int) ($input['page'] ?? 1), perPage: (int) ($input['per_page'] ?? 10),
        ));
    }
}
