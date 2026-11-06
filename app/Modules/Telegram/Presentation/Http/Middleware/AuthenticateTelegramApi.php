<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Presentation\Http\Middleware;

use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateTelegramApi
{
    public function __construct(private readonly TelegramLinkRepository $links) {}

    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        $secret = (string) config('telegram.api_secret');
        if (strlen($secret) < 32 || ! hash_equals($secret, (string) $request->header('X-Telegram-Api-Secret'))) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized.'], 401, ['Cache-Control' => 'no-store, private']);
        }
        $telegramId = (string) $request->header('X-Telegram-User-Id');
        $account = preg_match('/^[1-9][0-9]{0,15}$/D', $telegramId) && (int) $telegramId <= 4503599627370495
            ? $this->links->forTelegram(telegramUserId: (int) $telegramId) : null;
        if (! $account) {
            return response()->json(['ok' => false, 'message' => 'Telegram account is not linked.'], 403, ['Cache-Control' => 'no-store, private']);
        }
        $request->attributes->set('telegram.account', $account);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
