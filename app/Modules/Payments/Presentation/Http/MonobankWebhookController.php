<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\Services\MonobankCheckout;
use App\Modules\Payments\Infrastructure\Monobank\MonobankClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class MonobankWebhookController extends Controller
{
    public function __invoke(Request $request, MonobankClient $client, MonobankCheckout $checkout): JsonResponse
    {
        $body = $request->getContent();
        if (! $client->verifyWebhook($body, $request->header('X-Sign'))) {
            Log::warning('Monobank: неверная подпись вебхука', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 403);
        }
        $data = json_decode($body, true);
        if (is_array($data)) {
            $checkout->handleWebhook($data);
        }

        return response()->json(['ok' => true]);
    }
}
