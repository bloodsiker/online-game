<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateTelegramWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        $secret = (string) config('telegram.webhook_secret');
        if (strlen($secret) < 32 || ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return response()->json(['ok' => false], 403);
        }

        return $next($request);
    }
}
