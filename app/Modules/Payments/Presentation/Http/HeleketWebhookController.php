<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Application\Services\HeleketCheckout;
use App\Modules\Payments\Infrastructure\Heleket\HeleketClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class HeleketWebhookController extends Controller
{
    public function __invoke(Request $request, HeleketClient $client, HeleketCheckout $checkout): JsonResponse
    {
        $ips = (array) config('services.heleket.webhook_ips', []);
        if ($ips !== [] && ! in_array($request->ip(), $ips, true)) {
            Log::warning('Heleket: вебхук с чужого IP', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 403);
        }
        $data = json_decode($request->getContent(), true);
        if (! is_array($data) || ! $client->verifyWebhook($data)) {
            Log::warning('Heleket: неверная подпись вебхука', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 403);
        }

        $checkout->handleWebhook($data);

        return response()->json(['ok' => true]);
    }
}
