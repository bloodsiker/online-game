<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\Services\TrybitCheckout;
use App\Modules\Payments\Infrastructure\Trybit\TrybitClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TrybitWebhookController extends Controller
{
    public function __invoke(Request $request, TrybitClient $client, TrybitCheckout $checkout): JsonResponse
    {
        // POSTBACK приходит JSON или form-urlencoded — Laravel разбирает оба в input().
        $data = $request->all();
        if (! $client->verifyToken(is_string($data['token'] ?? null) ? $data['token'] : null)) {
            Log::warning('Trybit: неверный токен POSTBACK', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 401);
        }

        try {
            $checkout->handlePostback($data);
        } catch (Throwable $e) {
            // 5xx — Trybit повторит уведомление (или его можно отправить заново из «Истории POSTBACK»).
            Log::error('Trybit: ошибка обработки POSTBACK', ['error' => $e->getMessage(), 'invoice_id' => $data['invoice_id'] ?? null]);

            return response()->json(['ok' => false], 503);
        }

        return response()->json(['ok' => true]);
    }
}
